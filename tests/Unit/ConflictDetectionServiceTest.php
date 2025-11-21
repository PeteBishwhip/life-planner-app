<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\ConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConflictDetectionService $service;
    protected User $user;
    protected Calendar $personalCalendar;
    protected Calendar $businessCalendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ConflictDetectionService();
        $this->user = User::factory()->create();
        $this->personalCalendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'personal',
        ]);
        $this->businessCalendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'business',
        ]);
    }

    public function test_detects_conflict_with_overlapping_appointments(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Check for conflict with overlapping time
        $newStart = Carbon::parse('2025-01-01 10:30:00');
        $newEnd = Carbon::parse('2025-01-01 11:30:00');

        $hasConflict = $this->service->hasConflictAcrossCalendars(
            $this->user->id,
            $newStart,
            $newEnd
        );

        $this->assertTrue($hasConflict);
    }

    public function test_no_conflict_with_non_overlapping_appointments(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Check for conflict with non-overlapping time
        $newStart = Carbon::parse('2025-01-01 11:00:00');
        $newEnd = Carbon::parse('2025-01-01 12:00:00');

        $hasConflict = $this->service->hasConflictAcrossCalendars(
            $this->user->id,
            $newStart,
            $newEnd
        );

        $this->assertFalse($hasConflict);
    }

    public function test_detects_cross_calendar_conflicts(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create appointment in personal calendar
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Check for conflict in business calendar
        $newStart = Carbon::parse('2025-01-01 10:30:00');
        $newEnd = Carbon::parse('2025-01-01 11:30:00');

        $conflicts = $this->service->findConflicts(
            $this->user->id,
            $newStart,
            $newEnd
        );

        $this->assertCount(1, $conflicts);
        $this->assertEquals($this->personalCalendar->id, $conflicts->first()->calendar_id);
    }

    public function test_excludes_specific_appointment_from_conflict_check(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create existing appointment
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Check for conflict excluding the appointment itself (for updates)
        $hasConflict = $this->service->hasConflictAcrossCalendars(
            $this->user->id,
            $start,
            $end,
            $appointment->id
        );

        $this->assertFalse($hasConflict);
    }

    public function test_can_schedule_with_no_conflicts(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        $result = $this->service->canSchedule(
            $this->user->id,
            $this->personalCalendar->id,
            $start,
            $end
        );

        $this->assertTrue($result['can_schedule']);
        $this->assertEmpty($result['conflicts']);
    }

    public function test_cannot_schedule_with_conflicts_without_override(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Try to schedule conflicting appointment
        $newStart = Carbon::parse('2025-01-01 10:30:00');
        $newEnd = Carbon::parse('2025-01-01 11:30:00');

        $result = $this->service->canSchedule(
            $this->user->id,
            $this->businessCalendar->id,
            $newStart,
            $newEnd,
            null,
            false
        );

        $this->assertFalse($result['can_schedule']);
        $this->assertNotEmpty($result['conflicts']);
    }

    public function test_can_schedule_with_conflicts_and_override(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create existing appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Try to schedule conflicting appointment with override
        $newStart = Carbon::parse('2025-01-01 10:30:00');
        $newEnd = Carbon::parse('2025-01-01 11:30:00');

        $result = $this->service->canSchedule(
            $this->user->id,
            $this->businessCalendar->id,
            $newStart,
            $newEnd,
            null,
            true
        );

        $this->assertTrue($result['can_schedule']);
        $this->assertNotEmpty($result['conflicts']);
        $this->assertArrayHasKey('warning', $result);
    }

    public function test_gets_blocked_slots_for_calendar(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create appointment in business calendar
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->businessCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'scheduled',
        ]);

        // Get blocked slots for personal calendar
        $blockedSlots = $this->service->getBlockedSlots(
            $this->user->id,
            $this->personalCalendar->id,
            Carbon::parse('2025-01-01 00:00:00'),
            Carbon::parse('2025-01-01 23:59:59')
        );

        $this->assertCount(1, $blockedSlots);
        $this->assertTrue($blockedSlots->first()['is_blocking']);
    }

    public function test_finds_available_time_slots(): void
    {
        // Create appointment from 10:00 to 11:00
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => Carbon::parse('2025-01-01 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-01 11:00:00'),
            'status' => 'scheduled',
        ]);

        $availableSlots = $this->service->findAvailableSlots(
            $this->user->id,
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 12:00:00'),
            60, // 60 minute duration
            $this->personalCalendar->id,
            ['start' => 9, 'end' => 17]
        );

        $this->assertGreaterThan(0, $availableSlots->count());

        // Check that none of the available slots conflict with the existing appointment
        foreach ($availableSlots as $slot) {
            $slotStart = $slot['start'];
            $slotEnd = $slot['end'];

            $conflictCheck = !($slotStart->gte(Carbon::parse('2025-01-01 10:00:00')) &&
                              $slotStart->lt(Carbon::parse('2025-01-01 11:00:00')));

            $this->assertTrue($conflictCheck, 'Available slot should not conflict with existing appointment');
        }
    }

    public function test_calculates_conflict_percentage(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 12:00:00'); // 2 hours

        // Create conflicting appointment for 1 hour (50% overlap)
        $conflictingAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => Carbon::parse('2025-01-01 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-01 11:00:00'),
            'status' => 'scheduled',
        ]);

        $conflicts = collect([$conflictingAppointment]);

        $percentage = $this->service->calculateConflictPercentage($start, $end, $conflicts);

        $this->assertEquals(50.0, $percentage);
    }

    public function test_ignores_cancelled_appointments_in_conflict_detection(): void
    {
        $start = Carbon::parse('2025-01-01 10:00:00');
        $end = Carbon::parse('2025-01-01 11:00:00');

        // Create cancelled appointment
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'status' => 'cancelled',
        ]);

        // Check for conflict - should not find the cancelled appointment
        $hasConflict = $this->service->hasConflictAcrossCalendars(
            $this->user->id,
            $start,
            $end
        );

        $this->assertFalse($hasConflict);
    }
}
