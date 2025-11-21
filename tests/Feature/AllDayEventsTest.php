<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllDayEventsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_creates_all_day_event(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('appointments.store'), [
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Meeting',
            'start_datetime' => '2025-01-15',
            'end_datetime' => '2025-01-15',
            'is_all_day' => true,
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('appointments', [
            'title' => 'All Day Meeting',
            'is_all_day' => true,
        ]);

        $appointment = Appointment::where('title', 'All Day Meeting')->first();
        $this->assertTrue($appointment->is_all_day);
        $this->assertEquals('00:00:00', $appointment->start_datetime->format('H:i:s'));
        $this->assertEquals('23:59:59', $appointment->end_datetime->format('H:i:s'));
    }

    public function test_creates_multi_day_event(): void
    {
        $this->actingAs($this->user);

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Multi-day Conference',
            'start_datetime' => Carbon::parse('2025-01-15 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-17 23:59:59'),
            'is_all_day' => true,
        ]);

        $this->assertDatabaseHas('appointments', [
            'title' => 'Multi-day Conference',
            'is_all_day' => true,
        ]);

        $this->assertEquals(3, $appointment->start_datetime->diffInDays($appointment->end_datetime) + 1);
    }

    public function test_all_day_event_displays_without_time(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Birthday',
            'start_datetime' => Carbon::parse('2025-01-15 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 23:59:59'),
            'is_all_day' => true,
        ]);

        $this->assertTrue($appointment->is_all_day);
        $this->assertEquals('2025-01-15', $appointment->start_datetime->format('Y-m-d'));
    }

    public function test_regular_event_has_specific_time(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
            'is_all_day' => false,
        ]);

        $this->assertFalse($appointment->is_all_day);
        $this->assertEquals('10:00:00', $appointment->start_datetime->format('H:i:s'));
        $this->assertEquals('11:00:00', $appointment->end_datetime->format('H:i:s'));
    }

    public function test_filters_all_day_events(): void
    {
        // Create all-day event
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Event',
            'is_all_day' => true,
        ]);

        // Create regular event
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Regular Event',
            'is_all_day' => false,
        ]);

        $allDayEvents = Appointment::allDay()->get();

        $this->assertCount(1, $allDayEvents);
        $this->assertEquals('All Day Event', $allDayEvents->first()->title);
    }

    public function test_all_day_event_does_not_conflict_with_timed_event(): void
    {
        // Create all-day event
        $allDayEvent = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Event',
            'start_datetime' => Carbon::parse('2025-01-15 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 23:59:59'),
            'is_all_day' => true,
            'status' => 'scheduled',
        ]);

        // Check if specific time on same day conflicts
        $hasConflict = $allDayEvent->hasConflict(
            $this->calendar->id,
            Carbon::parse('2025-01-15 10:00:00'),
            Carbon::parse('2025-01-15 11:00:00')
        );

        // All-day events should technically conflict with any time on that day
        $this->assertTrue($hasConflict);
    }

    public function test_multi_day_all_day_event_spans_correct_days(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Vacation',
            'start_datetime' => Carbon::parse('2025-01-15 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-20 23:59:59'),
            'is_all_day' => true,
        ]);

        $startDate = Carbon::parse('2025-01-10');
        $endDate = Carbon::parse('2025-01-25');

        $appointments = Appointment::betweenDates($startDate, $endDate)->get();

        $this->assertCount(1, $appointments);
        $this->assertEquals('Vacation', $appointments->first()->title);

        // Verify it spans the correct number of days (6 days: 15-20 inclusive)
        $daySpan = $appointment->start_datetime->diffInDays($appointment->end_datetime) + 1;
        $this->assertEquals(6, $daySpan);
    }
}
