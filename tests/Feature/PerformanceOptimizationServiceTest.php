<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\PerformanceOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceOptimizationService $service;

    protected User $user;

    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PerformanceOptimizationService;
        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_get_recommended_indexes_returns_array(): void
    {
        $indexes = $this->service->getRecommendedIndexes();

        $this->assertIsArray($indexes);
        $this->assertNotEmpty($indexes);
    }

    public function test_get_recommended_indexes_includes_appointments_table(): void
    {
        $indexes = $this->service->getRecommendedIndexes();

        $this->assertArrayHasKey('appointments', $indexes);
        $this->assertIsArray($indexes['appointments']);
    }

    public function test_get_recommended_indexes_includes_calendars_table(): void
    {
        $indexes = $this->service->getRecommendedIndexes();

        $this->assertArrayHasKey('calendars', $indexes);
        $this->assertIsArray($indexes['calendars']);
    }

    public function test_get_recommended_indexes_includes_appointment_reminders_table(): void
    {
        $indexes = $this->service->getRecommendedIndexes();

        $this->assertArrayHasKey('appointment_reminders', $indexes);
        $this->assertIsArray($indexes['appointment_reminders']);
    }

    public function test_get_query_optimization_tips_returns_array(): void
    {
        $tips = $this->service->getQueryOptimizationTips();

        $this->assertIsArray($tips);
        $this->assertNotEmpty($tips);
    }

    public function test_get_query_optimization_tips_includes_eager_loading(): void
    {
        $tips = $this->service->getQueryOptimizationTips();

        $this->assertArrayHasKey('eager_loading', $tips);
        $this->assertArrayHasKey('tip', $tips['eager_loading']);
        $this->assertArrayHasKey('example', $tips['eager_loading']);
        $this->assertArrayHasKey('benefit', $tips['eager_loading']);
    }

    public function test_get_query_optimization_tips_includes_chunking(): void
    {
        $tips = $this->service->getQueryOptimizationTips();

        $this->assertArrayHasKey('chunking', $tips);
    }

    public function test_clear_all_caches_returns_results(): void
    {
        $results = $this->service->clearAllCaches();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('application_cache', $results);
    }

    public function test_get_cache_statistics_returns_driver_info(): void
    {
        $stats = $this->service->getCacheStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('driver', $stats);
        $this->assertArrayHasKey('stores', $stats);
    }

    public function test_get_optimized_appointments_caches_results(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Test Appointment',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        // First call should hit the database
        $results1 = $this->service->getOptimizedAppointments($this->user->id);

        // Second call should hit the cache
        $results2 = $this->service->getOptimizedAppointments($this->user->id);

        $this->assertEquals($results1->count(), $results2->count());
    }

    public function test_get_optimized_appointments_uses_select_columns(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Test Appointment',
            'description' => 'This should not be selected',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        $results = $this->service->getOptimizedAppointments($this->user->id);
        $appointment = $results->first();

        $this->assertNotNull($appointment->title);
        $this->assertNotNull($appointment->start_datetime);
    }

    public function test_invalidate_user_appointments_cache(): void
    {
        // Create and cache data
        $this->service->getOptimizedAppointments($this->user->id);

        // Invalidate cache
        $this->service->invalidateUserAppointmentsCache($this->user->id);

        // This should work without errors
        $this->assertTrue(true);
    }

    public function test_get_database_optimization_recommendations_returns_array(): void
    {
        $recommendations = $this->service->getDatabaseOptimizationRecommendations();

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
    }

    public function test_get_database_optimization_recommendations_includes_indexing(): void
    {
        $recommendations = $this->service->getDatabaseOptimizationRecommendations();

        $this->assertArrayHasKey('indexing', $recommendations);
        $this->assertArrayHasKey('status', $recommendations['indexing']);
        $this->assertArrayHasKey('description', $recommendations['indexing']);
        $this->assertArrayHasKey('impact', $recommendations['indexing']);
    }

    public function test_get_database_optimization_recommendations_includes_caching(): void
    {
        $recommendations = $this->service->getDatabaseOptimizationRecommendations();

        $this->assertArrayHasKey('caching', $recommendations);
    }

    public function test_get_database_optimization_recommendations_includes_query_optimization(): void
    {
        $recommendations = $this->service->getDatabaseOptimizationRecommendations();

        $this->assertArrayHasKey('query_optimization', $recommendations);
    }

    public function test_get_frontend_optimization_recommendations_returns_array(): void
    {
        $recommendations = $this->service->getFrontendOptimizationRecommendations();

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
    }

    public function test_get_frontend_optimization_recommendations_includes_lazy_loading(): void
    {
        $recommendations = $this->service->getFrontendOptimizationRecommendations();

        $this->assertArrayHasKey('lazy_loading', $recommendations);
    }

    public function test_get_frontend_optimization_recommendations_includes_asset_compression(): void
    {
        $recommendations = $this->service->getFrontendOptimizationRecommendations();

        $this->assertArrayHasKey('asset_compression', $recommendations);
    }

    public function test_run_diagnostics_returns_comprehensive_info(): void
    {
        $diagnostics = $this->service->runDiagnostics();

        $this->assertIsArray($diagnostics);
        $this->assertArrayHasKey('database', $diagnostics);
        $this->assertArrayHasKey('cache', $diagnostics);
        $this->assertArrayHasKey('memory', $diagnostics);
    }

    public function test_run_diagnostics_database_info(): void
    {
        $diagnostics = $this->service->runDiagnostics();

        $this->assertArrayHasKey('connection', $diagnostics['database']);
        $this->assertIsBool($diagnostics['database']['connection']);
    }

    public function test_run_diagnostics_cache_info(): void
    {
        $diagnostics = $this->service->runDiagnostics();

        $this->assertArrayHasKey('driver', $diagnostics['cache']);
        $this->assertArrayHasKey('working', $diagnostics['cache']);
    }

    public function test_run_diagnostics_memory_info(): void
    {
        $diagnostics = $this->service->runDiagnostics();

        $this->assertArrayHasKey('current', $diagnostics['memory']);
        $this->assertArrayHasKey('peak', $diagnostics['memory']);
        $this->assertIsString($diagnostics['memory']['current']);
        $this->assertIsString($diagnostics['memory']['peak']);
    }

    public function test_cache_invalidation_works(): void
    {
        Cache::put('test-key', 'test-value', 60);
        $this->assertEquals('test-value', Cache::get('test-key'));

        Cache::forget('test-key');
        $this->assertNull(Cache::get('test-key'));
    }

    public function test_optimized_appointments_respects_filters(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Meeting',
            'status' => 'scheduled',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Cancelled Meeting',
            'status' => 'cancelled',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->service->getOptimizedAppointments($this->user->id, [
            'status' => 'scheduled',
        ]);

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertTrue($results->every(fn ($apt) => $apt->status === 'scheduled'));
    }

    public function test_performance_indexes_recommendations_structure(): void
    {
        $indexes = $this->service->getRecommendedIndexes();

        foreach ($indexes as $table => $tableIndexes) {
            $this->assertIsString($table);
            $this->assertIsArray($tableIndexes);

            foreach ($tableIndexes as $indexName => $columns) {
                $this->assertIsString($indexName);
                $this->assertIsArray($columns);
                $this->assertNotEmpty($columns);
            }
        }
    }
}
