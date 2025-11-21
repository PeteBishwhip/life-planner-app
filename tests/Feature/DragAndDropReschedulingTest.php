<?php

namespace Tests\Feature;

use App\Livewire\AppointmentManager;
use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DragAndDropReschedulingTest extends TestCase
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

    public function test_reschedules_appointment_to_new_time(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:00:00'
            );

        $appointment->refresh();

        $this->assertEquals('2025-01-15 14:00:00', $appointment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-15 15:00:00', $appointment->end_datetime->format('Y-m-d H:i:s'));
    }

    public function test_reschedules_appointment_to_different_day(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-16 10:00:00',
                '2025-01-16 11:00:00'
            );

        $appointment->refresh();

        $this->assertEquals('2025-01-16', $appointment->start_datetime->format('Y-m-d'));
    }

    public function test_maintains_appointment_duration_when_rescheduling(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:30:00'), // 90 minutes
        ]);

        $originalDuration = $appointment->getDurationInMinutes();

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:30:00' // Same 90 minutes duration
            );

        $appointment->refresh();

        $this->assertEquals($originalDuration, $appointment->getDurationInMinutes());
    }

    public function test_cannot_reschedule_another_users_appointment(): void
    {
        $otherUser = User::factory()->create();
        $otherCalendar = Calendar::factory()->create(['user_id' => $otherUser->id]);

        $appointment = Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'calendar_id' => $otherCalendar->id,
            'title' => 'Other User Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:00:00'
            );
    }

    public function test_reschedules_all_day_event(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Event',
            'start_datetime' => Carbon::parse('2025-01-15 00:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 23:59:59'),
            'is_all_day' => true,
        ]);

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-16 00:00:00',
                '2025-01-16 23:59:59'
            );

        $appointment->refresh();

        $this->assertEquals('2025-01-16', $appointment->start_datetime->format('Y-m-d'));
        $this->assertTrue($appointment->is_all_day);
    }

    public function test_dispatches_event_after_rescheduling(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:00:00'
            )
            ->assertDispatched('appointment-saved');
    }

    public function test_preserves_appointment_details_when_rescheduling(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Important Meeting',
            'description' => 'Meeting description',
            'location' => 'Conference Room A',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
            'color' => '#FF5733',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->user);

        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:00:00'
            );

        $appointment->refresh();

        // Verify only datetime changed, everything else preserved
        $this->assertEquals('Important Meeting', $appointment->title);
        $this->assertEquals('Meeting description', $appointment->description);
        $this->assertEquals('Conference Room A', $appointment->location);
        $this->assertEquals('#FF5733', $appointment->color);
        $this->assertEquals('scheduled', $appointment->status);
        $this->assertEquals('2025-01-15 14:00:00', $appointment->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_reschedules_recurring_appointment_instance(): void
    {
        // Note: This test assumes that rescheduling a recurring appointment
        // creates an exception for that instance
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Recurring Meeting',
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
            'recurrence_rule' => [
                'frequency' => 'daily',
                'interval' => 1,
                'count' => 5,
            ],
        ]);

        $this->actingAs($this->user);

        // Reschedule the appointment
        Livewire::test(AppointmentManager::class)
            ->call('reschedule',
                $appointment->id,
                '2025-01-15 14:00:00',
                '2025-01-15 15:00:00'
            );

        $appointment->refresh();

        // Verify the appointment was rescheduled
        $this->assertEquals('2025-01-15 14:00:00', $appointment->start_datetime->format('Y-m-d H:i:s'));
    }
}
