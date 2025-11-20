<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->for($this->user)->create();
    }

    /** @test */
    public function it_can_create_an_appointment(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create([
                'title' => 'Team Meeting',
            ]);

        $this->assertDatabaseHas('appointments', [
            'title' => 'Team Meeting',
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_calendar(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create();

        $this->assertInstanceOf(Calendar::class, $appointment->calendar);
        $this->assertEquals($this->calendar->id, $appointment->calendar_id);
    }

    /** @test */
    public function it_belongs_to_a_user(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create();

        $this->assertInstanceOf(User::class, $appointment->user);
        $this->assertEquals($this->user->id, $appointment->user_id);
    }

    /** @test */
    public function it_has_many_reminders(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create();

        AppointmentReminder::factory()
            ->count(3)
            ->for($appointment)
            ->create();

        $this->assertCount(3, $appointment->reminders);
        $this->assertInstanceOf(AppointmentReminder::class, $appointment->reminders->first());
    }

    /** @test */
    public function it_casts_dates_correctly(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $appointment->start_datetime);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $appointment->end_datetime);
    }

    /** @test */
    public function it_casts_recurrence_rule_as_array(): void
    {
        $recurrenceRule = [
            'freq' => 'weekly',
            'interval' => 1,
            'count' => 10,
        ];

        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create(['recurrence_rule' => $recurrenceRule]);

        $this->assertIsArray($appointment->recurrence_rule);
        $this->assertEquals('weekly', $appointment->recurrence_rule['freq']);
    }

    /** @test */
    public function it_sets_default_status_to_scheduled(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create(['status' => null]);

        $this->assertEquals('scheduled', $appointment->status);
    }

    /** @test */
    public function it_inherits_color_from_calendar_if_not_provided(): void
    {
        $this->calendar->update(['color' => '#FF5733']);

        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create(['color' => null]);

        $this->assertEquals('#FF5733', $appointment->color);
    }

    /** @test */
    public function scope_for_user_filters_by_user_id(): void
    {
        $anotherUser = User::factory()->create();
        $anotherCalendar = Calendar::factory()->for($anotherUser)->create();

        Appointment::factory()->count(2)->for($this->calendar)->for($this->user)->create();
        Appointment::factory()->count(3)->for($anotherCalendar)->for($anotherUser)->create();

        $userAppointments = Appointment::forUser($this->user->id)->get();

        $this->assertCount(2, $userAppointments);
    }

    /** @test */
    public function scope_for_calendar_filters_by_calendar_id(): void
    {
        $anotherCalendar = Calendar::factory()->for($this->user)->create();

        Appointment::factory()->count(3)->for($this->calendar)->for($this->user)->create();
        Appointment::factory()->count(2)->for($anotherCalendar)->for($this->user)->create();

        $calendarAppointments = Appointment::forCalendar($this->calendar->id)->get();

        $this->assertCount(3, $calendarAppointments);
    }

    /** @test */
    public function scope_between_dates_finds_appointments_in_range(): void
    {
        $now = now();

        // Appointment within range
        Appointment::factory()->for($this->calendar)->for($this->user)->create([
            'start_datetime' => $now->copy()->addDays(5),
            'end_datetime' => $now->copy()->addDays(5)->addHours(2),
        ]);

        // Appointment outside range
        Appointment::factory()->for($this->calendar)->for($this->user)->create([
            'start_datetime' => $now->copy()->addDays(20),
            'end_datetime' => $now->copy()->addDays(20)->addHours(2),
        ]);

        $appointments = Appointment::betweenDates(
            $now->copy()->startOfDay(),
            $now->copy()->addDays(10)->endOfDay()
        )->get();

        $this->assertCount(1, $appointments);
    }

    /** @test */
    public function scope_upcoming_finds_future_scheduled_appointments(): void
    {
        Appointment::factory()->for($this->calendar)->for($this->user)->upcoming()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->past()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->cancelled()->create();

        $upcomingAppointments = Appointment::upcoming()->get();

        $this->assertCount(1, $upcomingAppointments);
    }

    /** @test */
    public function scope_scheduled_filters_scheduled_appointments(): void
    {
        Appointment::factory()->for($this->calendar)->for($this->user)->scheduled()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->completed()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->cancelled()->create();

        $scheduledAppointments = Appointment::scheduled()->get();

        $this->assertCount(1, $scheduledAppointments);
    }

    /** @test */
    public function scope_completed_filters_completed_appointments(): void
    {
        Appointment::factory()->for($this->calendar)->for($this->user)->scheduled()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->completed()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->completed()->create();

        $completedAppointments = Appointment::completed()->get();

        $this->assertCount(2, $completedAppointments);
    }

    /** @test */
    public function scope_cancelled_filters_cancelled_appointments(): void
    {
        Appointment::factory()->for($this->calendar)->for($this->user)->scheduled()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->cancelled()->create();

        $cancelledAppointments = Appointment::cancelled()->get();

        $this->assertCount(1, $cancelledAppointments);
    }

    /** @test */
    public function scope_all_day_filters_all_day_appointments(): void
    {
        Appointment::factory()->for($this->calendar)->for($this->user)->allDay()->create();
        Appointment::factory()->for($this->calendar)->for($this->user)->create(['is_all_day' => false]);

        $allDayAppointments = Appointment::allDay()->get();

        $this->assertCount(1, $allDayAppointments);
    }

    /** @test */
    public function is_recurring_returns_true_when_recurrence_rule_exists(): void
    {
        $recurringAppointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->recurring()
            ->create();

        $normalAppointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create();

        $this->assertTrue($recurringAppointment->isRecurring());
        $this->assertFalse($normalAppointment->isRecurring());
    }

    /** @test */
    public function has_conflict_detects_overlapping_appointments(): void
    {
        $existingAppointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create([
                'start_datetime' => now()->addHours(1),
                'end_datetime' => now()->addHours(2),
                'status' => 'scheduled',
            ]);

        // Overlapping time
        $hasConflict = (new Appointment())->hasConflict(
            $this->calendar->id,
            now()->addHours(1)->addMinutes(30),
            now()->addHours(2)->addMinutes(30)
        );

        $this->assertTrue($hasConflict);

        // Non-overlapping time
        $noConflict = (new Appointment())->hasConflict(
            $this->calendar->id,
            now()->addHours(3),
            now()->addHours(4)
        );

        $this->assertFalse($noConflict);
    }

    /** @test */
    public function get_duration_in_minutes_returns_correct_duration(): void
    {
        $appointment = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create([
                'start_datetime' => now(),
                'end_datetime' => now()->addMinutes(90),
            ]);

        $this->assertEquals(90, $appointment->getDurationInMinutes());
    }

    /** @test */
    public function it_can_have_recurrence_parent_and_instances(): void
    {
        $parent = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->recurring()
            ->create();

        $instance = Appointment::factory()
            ->for($this->calendar)
            ->for($this->user)
            ->create(['recurrence_parent_id' => $parent->id]);

        $this->assertInstanceOf(Appointment::class, $instance->recurrenceParent);
        $this->assertEquals($parent->id, $instance->recurrenceParent->id);
        $this->assertCount(1, $parent->recurrenceInstances);
    }
}
