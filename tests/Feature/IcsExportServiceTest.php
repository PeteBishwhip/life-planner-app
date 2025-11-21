<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\IcsExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IcsExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Calendar $calendar;

    protected IcsExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create(['user_id' => $this->user->id]);
        $this->service = new IcsExportService;
    }

    /** @test */
    public function it_can_export_a_calendar_to_ics_format()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Appointment',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $icsContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('BEGIN:VEVENT', $icsContent);
        $this->assertStringContainsString('Test Appointment', $icsContent);
        $this->assertStringContainsString('END:VEVENT', $icsContent);
        $this->assertStringContainsString('END:VCALENDAR', $icsContent);
    }

    /** @test */
    public function it_can_export_multiple_appointments()
    {
        Appointment::factory()->count(3)->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
        ]);

        $icsContent = $this->service->exportCalendar($this->calendar);

        // Count VEVENT occurrences
        $eventCount = substr_count($icsContent, 'BEGIN:VEVENT');
        $this->assertEquals(3, $eventCount);
    }

    /** @test */
    public function it_can_export_calendar_with_date_range_filter()
    {
        // Create appointments in different dates
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
            'title' => 'February Event',
            'start_datetime' => Carbon::parse('2025-02-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-02-15 11:00:00'),
        ]);

        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'March Event',
            'start_datetime' => Carbon::parse('2025-03-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-03-15 11:00:00'),
        ]);

        // Export only January to February
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-02-28');
        $icsContent = $this->service->exportCalendar($this->calendar, $startDate, $endDate);

        $this->assertStringContainsString('January Event', $icsContent);
        $this->assertStringContainsString('February Event', $icsContent);
        $this->assertStringNotContainsString('March Event', $icsContent);
    }

    /** @test */
    public function it_can_export_all_day_events()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'All Day Event',
            'start_datetime' => Carbon::parse('2025-01-20 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-20 23:59:59'),
            'is_all_day' => true,
        ]);

        $icsContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('All Day Event', $icsContent);
        // All day events should have specific formatting in ICS
        $this->assertStringContainsString('BEGIN:VEVENT', $icsContent);
    }

    /** @test */
    public function it_can_export_appointments_with_location_and_description()
    {
        Appointment::factory()->create([
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Meeting',
            'description' => 'Important meeting description',
            'location' => 'Conference Room A',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $icsContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('Meeting', $icsContent);
        $this->assertStringContainsString('Important meeting description', $icsContent);
        $this->assertStringContainsString('Conference Room A', $icsContent);
    }

    /** @test */
    public function it_can_export_multiple_calendars_combined()
    {
        $calendar2 = Calendar::factory()->create(['user_id' => $this->user->id]);

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

        $icsContent = $this->service->exportMultipleCalendars([$this->calendar, $calendar2]);

        $this->assertStringContainsString('Calendar 1 Event', $icsContent);
        $this->assertStringContainsString('Calendar 2 Event', $icsContent);
        $this->assertEquals(2, substr_count($icsContent, 'BEGIN:VEVENT'));
    }

    /** @test */
    public function it_generates_valid_filename()
    {
        $filename = $this->service->generateFilename($this->calendar);

        $this->assertStringContainsString('.ics', $filename);
        $this->assertStringContainsString(Carbon::now()->format('Y-m-d'), $filename);
    }

    /** @test */
    public function it_returns_correct_mime_type()
    {
        $mimeType = $this->service->getMimeType();

        $this->assertEquals('text/calendar', $mimeType);
    }

    /** @test */
    public function it_exports_recurring_appointments()
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

        $icsContent = $this->service->exportCalendar($this->calendar);

        $this->assertStringContainsString('Weekly Meeting', $icsContent);
        $this->assertStringContainsString('RRULE:', $icsContent);
        $this->assertStringContainsString('FREQ=WEEKLY', $icsContent);
    }
}
