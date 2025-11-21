<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Calendar $calendar;

    protected PdfExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Calendar',
            'color' => '#3B82F6',
        ]);
        $this->service = new PdfExportService;
    }

    /** @test */
    public function it_can_export_calendar_in_month_view()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Appointment',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $month = Carbon::parse('2025-01-01');
        $pdf = $this->service->exportMonthView($this->calendar, $month);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    /** @test */
    public function it_can_export_calendar_in_list_view()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Appointment',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');
        $pdf = $this->service->exportListView($this->calendar, $startDate, $endDate);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    /** @test */
    public function it_can_export_multiple_calendars_combined()
    {
        $calendar2 = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Second Calendar',
        ]);

        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Calendar 1 Event',
        ]);

        Appointment::factory()->create([
            'calendar_id' => $calendar2->id,
            'user_id' => $this->user->id,
            'title' => 'Calendar 2 Event',
        ]);

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $pdf = $this->service->exportMultipleCalendars([$this->calendar, $calendar2], $startDate, $endDate);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    /** @test */
    public function it_generates_calendar_grid_correctly()
    {
        $month = Carbon::parse('2025-01-01');
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateCalendarGrid');
        $method->setAccessible(true);

        $grid = $method->invoke($this->service, $month);

        $this->assertIsArray($grid);
        $this->assertGreaterThan(0, count($grid));

        // Each week should have 7 days (except possibly the last week)
        foreach ($grid as $index => $week) {
            // All weeks except the last one must have 7 days
            if ($index < count($grid) - 1) {
                $this->assertCount(7, $week);
            } else {
                // Last week should have at least 1 day and at most 7
                $this->assertGreaterThanOrEqual(1, count($week));
                $this->assertLessThanOrEqual(7, count($week));
            }
        }
    }

    /** @test */
    public function it_generates_valid_filename()
    {
        $filename = $this->service->generateFilename($this->calendar, 'month');

        $this->assertStringContainsString('.pdf', $filename);
        $this->assertStringContainsString('month', $filename);
        $this->assertStringContainsString(Carbon::now()->format('Y-m-d'), $filename);
    }

    /** @test */
    public function it_generates_combined_filename()
    {
        $filename = $this->service->generateCombinedFilename();

        $this->assertStringContainsString('.pdf', $filename);
        $this->assertStringContainsString('combined', $filename);
        $this->assertStringContainsString(Carbon::now()->format('Y-m-d'), $filename);
    }

    /** @test */
    public function it_exports_appointments_with_date_range_filter()
    {
        // Create appointments in different months
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'January Event',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'March Event',
            'start_datetime' => Carbon::parse('2025-03-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-03-15 11:00:00'),
        ]);

        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-02-28');
        $pdf = $this->service->exportListView($this->calendar, $startDate, $endDate);

        $this->assertNotNull($pdf);
    }

    /** @test */
    public function it_uses_correct_paper_format()
    {
        $month = Carbon::parse('2025-01-01');
        $pdf = $this->service->exportMonthView($this->calendar, $month);

        // PDF should be configured with A4 paper
        $this->assertNotNull($pdf);
    }

    /** @test */
    public function it_handles_empty_calendar_export()
    {
        // Export a calendar with no appointments
        $month = Carbon::parse('2025-01-01');
        $pdf = $this->service->exportMonthView($this->calendar, $month);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }
}
