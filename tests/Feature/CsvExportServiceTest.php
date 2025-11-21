<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\CsvExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Calendar $calendar;

    protected CsvExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Calendar',
        ]);
        $this->service = new CsvExportService;
    }

    /** @test */
    public function it_can_export_calendar_to_csv_format()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Appointment',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $csvContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('Title,Description,Location', $csvContent);
        $this->assertStringContainsString('Test Appointment', $csvContent);
        $this->assertStringContainsString('Test Calendar', $csvContent);
    }

    /** @test */
    public function it_includes_all_required_columns_in_csv_header()
    {
        $csvContent = $this->service->exportCalendar($this->calendar);

        $lines = explode("\n", trim($csvContent));
        $header = str_getcsv($lines[0]);

        $expectedColumns = [
            'Title',
            'Description',
            'Location',
            'Start Date',
            'Start Time',
            'End Date',
            'End Time',
            'All Day',
            'Calendar',
            'Calendar Type',
            'Status',
            'Is Recurring',
            'Recurrence Pattern',
            'Created At',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $header);
        }
    }

    /** @test */
    public function it_exports_multiple_appointments()
    {
        // Ensure we start with a clean slate
        $initialCount = $this->calendar->appointments()->count();
        $this->assertEquals(0, $initialCount, 'Calendar should start with no appointments');

        Appointment::factory()->count(3)->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals(3, $this->calendar->appointments()->count(), 'Should have exactly 3 appointments');

        $csvContent = $this->service->exportCalendar($this->calendar);

        $lines = array_filter(explode("\n", $csvContent), fn ($line) => trim($line) !== '');
        // 1 header + 3 appointments = 4 lines
        $this->assertCount(4, $lines);
    }

    /** @test */
    public function it_formats_all_day_events_correctly()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'All Day Event',
            'start_datetime' => Carbon::parse('2025-01-20 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-20 23:59:59'),
            'is_all_day' => true,
        ]);

        $csvContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('All Day Event', $csvContent);
        $this->assertStringContainsString('Yes', $csvContent); // All Day column should show "Yes"
    }

    /** @test */
    public function it_exports_appointments_with_date_range_filter()
    {
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
        $csvContent = $this->service->exportCalendar($this->calendar, $startDate, $endDate);

        $this->assertStringContainsString('January Event', $csvContent);
        $this->assertStringNotContainsString('March Event', $csvContent);
    }

    /** @test */
    public function it_exports_appointment_with_all_details()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Detailed Meeting',
            'description' => 'Important meeting',
            'location' => 'Conference Room',
            'start_datetime' => Carbon::parse('2025-01-15 14:30:00'),
            'end_datetime' => Carbon::parse('2025-01-15 15:30:00'),
            'status' => 'scheduled',
        ]);

        $csvContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('Detailed Meeting', $csvContent);
        $this->assertStringContainsString('Important meeting', $csvContent);
        $this->assertStringContainsString('Conference Room', $csvContent);
        $this->assertStringContainsString('2025-01-15', $csvContent);
        $this->assertStringContainsString('14:30', $csvContent);
        $this->assertStringContainsString('scheduled', $csvContent);
    }

    /** @test */
    public function it_exports_recurring_appointments_with_pattern_description()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Weekly Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
            'recurrence_rule' => [
                'freq' => 'weekly',
                'interval' => 1,
                'count' => 10,
            ],
        ]);

        $csvContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('Weekly Meeting', $csvContent);
        $this->assertStringContainsString('Yes', $csvContent); // Is Recurring column
        $this->assertStringContainsString('Weekly', $csvContent); // Recurrence Pattern
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

        $csvContent = $this->service->exportMultipleCalendars([$this->calendar, $calendar2]);

        $this->assertStringContainsString('Calendar 1 Event', $csvContent);
        $this->assertStringContainsString('Calendar 2 Event', $csvContent);
        $this->assertStringContainsString('Test Calendar', $csvContent);
        $this->assertStringContainsString('Second Calendar', $csvContent);
    }

    /** @test */
    public function it_generates_valid_filename()
    {
        $filename = $this->service->generateFilename($this->calendar);

        $this->assertStringContainsString('.csv', $filename);
        $this->assertStringContainsString(Carbon::now()->format('Y-m-d'), $filename);
    }

    /** @test */
    public function it_returns_correct_mime_type()
    {
        $mimeType = $this->service->getMimeType();

        $this->assertEquals('text/csv', $mimeType);
    }

    /** @test */
    public function it_handles_empty_calendar_export()
    {
        $csvContent = $this->service->exportCalendar($this->calendar);

        // Should still have header row
        $lines = explode("\n", trim($csvContent));
        $this->assertCount(1, $lines); // Only header, no data rows
    }
}
