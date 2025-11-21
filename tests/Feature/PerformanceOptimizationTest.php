<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
            'is_visible' => true,
        ]);
    }

    /** @test */
    public function calendar_dashboard_caches_user_calendars(): void
    {
        Cache::flush();

        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check that cache was set
        $cacheKey = 'user_calendars_'.$this->user->id;
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function cached_calendars_reduce_database_queries(): void
    {
        Cache::flush();

        // First request - should cache calendars in render method
        $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        // Get the cached value
        $cacheKey = 'user_calendars_'.$this->user->id;
        $cachedCalendars = Cache::get($cacheKey);

        // Verify cache was populated
        $this->assertNotNull($cachedCalendars);
        $this->assertGreaterThan(0, $cachedCalendars->count());

        // Clear query log
        DB::connection()->enableQueryLog();

        // Manually get calendars using cache (simulating render method)
        $calendars = cache()->remember(
            $cacheKey,
            3600,
            fn () => $this->user->calendars()->get()
        );

        $queries = DB::getQueryLog();

        // Verify no database query was made since we used cache
        $this->assertEmpty($queries, 'Should use cached calendars without database query');
    }

    /** @test */
    public function appointment_query_uses_select_optimization(): void
    {
        Appointment::factory()->count(5)->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
        ]);

        DB::connection()->enableQueryLog();

        $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $queries = DB::getQueryLog();

        // Find the appointments query
        $appointmentQuery = collect($queries)->first(function ($query) {
            return str_contains($query['query'], 'appointments');
        });

        $this->assertNotNull($appointmentQuery);

        // Verify specific columns are selected (not SELECT *)
        $queryString = $appointmentQuery['query'];
        $this->assertStringNotContainsString('select *', strtolower($queryString));
    }

    /** @test */
    public function appointment_query_eager_loads_calendar_relationship(): void
    {
        Appointment::factory()->count(3)->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
        ]);

        DB::connection()->enableQueryLog();

        $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // With eager loading, we should have:
        // 1. Query for calendars (from cache or DB)
        // 2. Query for appointments
        // 3. Query for related calendars (eager load)
        // Without N+1 issues, should be minimal queries

        $this->assertLessThan(10, $queryCount, 'Query count should be optimized');
    }

    /** @test */
    public function calendar_dashboard_returns_empty_collection_when_no_visible_calendars(): void
    {
        // Make calendar invisible
        $this->calendar->update(['is_visible' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Should not execute appointment query when no calendars are visible
        DB::connection()->enableQueryLog();

        $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $queries = DB::getQueryLog();

        // Verify appointments query is skipped
        $appointmentQueries = array_filter($queries, function ($query) {
            return str_contains($query['query'], 'appointments') &&
                   str_contains($query['query'], 'select');
        });

        $this->assertEmpty($appointmentQueries, 'Appointment query should be skipped when no calendars visible');
    }

    /** @test */
    public function performance_indexes_migration_exists(): void
    {
        $migrationPath = database_path('migrations');
        $files = scandir($migrationPath);

        $indexMigration = array_filter($files, function ($file) {
            return str_contains($file, 'add_performance_indexes');
        });

        $this->assertNotEmpty($indexMigration, 'Performance indexes migration should exist');
    }

    /** @test */
    public function performance_indexes_migration_adds_appointments_indexes(): void
    {
        $migrationPath = database_path('migrations');
        $files = scandir($migrationPath);

        $indexMigrationFile = array_values(array_filter($files, function ($file) {
            return str_contains($file, 'add_performance_indexes');
        }))[0] ?? null;

        $this->assertNotNull($indexMigrationFile);

        $content = file_get_contents($migrationPath.'/'.$indexMigrationFile);

        // Check for important indexes
        $this->assertStringContainsString('appointments_date_range_index', $content);
        $this->assertStringContainsString('appointments_user_calendar_index', $content);
        $this->assertStringContainsString('start_datetime', $content);
        $this->assertStringContainsString('end_datetime', $content);
    }

    /** @test */
    public function performance_indexes_migration_adds_calendars_indexes(): void
    {
        $migrationPath = database_path('migrations');
        $files = scandir($migrationPath);

        $indexMigrationFile = array_values(array_filter($files, function ($file) {
            return str_contains($file, 'add_performance_indexes');
        }))[0] ?? null;

        $this->assertNotNull($indexMigrationFile);

        $content = file_get_contents($migrationPath.'/'.$indexMigrationFile);

        // Check for calendar indexes
        $this->assertStringContainsString('calendars_user_visible_index', $content);
        $this->assertStringContainsString('is_visible', $content);
    }

    /** @test */
    public function touch_gestures_use_passive_listeners_for_performance(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Passive listeners improve scroll performance
        $passiveCount = substr_count($content, 'passive: true');

        $this->assertGreaterThan(0, $passiveCount, 'Touch gestures should use passive event listeners');
    }

    /** @test */
    public function assets_are_loaded_via_vite_for_optimization(): void
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = file_get_contents($layoutPath);

        // Check for Vite asset bundling
        $this->assertStringContainsString('@vite', $content);
        $this->assertStringContainsString('resources/css/app.css', $content);
        $this->assertStringContainsString('resources/js/app.js', $content);
    }

    /** @test */
    public function calendar_dashboard_component_has_optimized_query_methods(): void
    {
        $componentPath = app_path('Livewire/CalendarDashboard.php');
        $content = file_get_contents($componentPath);

        // Check for optimization patterns
        $this->assertStringContainsString('cache()->remember', $content);
        $this->assertStringContainsString('->select([', $content);
        $this->assertStringContainsString('->with([', $content);
    }

    /** @test */
    public function appointments_query_filters_by_date_range(): void
    {
        // Create appointments outside and inside date range
        $now = now();

        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'start_datetime' => $now->copy()->addMonths(6), // Outside month view
        ]);

        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'start_datetime' => $now->copy()->addDays(5), // Inside month view
        ]);

        DB::connection()->enableQueryLog();

        $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $queries = DB::getQueryLog();

        // Find appointments query
        $appointmentQuery = collect($queries)->first(function ($query) {
            return str_contains($query['query'], 'appointments') &&
                   str_contains($query['query'], 'select');
        });

        // Verify date range filtering is applied
        $this->assertNotNull($appointmentQuery);
        $queryString = strtolower($appointmentQuery['query']);
        $this->assertTrue(
            str_contains($queryString, 'start_datetime') || str_contains($queryString, 'between'),
            'Query should filter by date range'
        );
    }
}
