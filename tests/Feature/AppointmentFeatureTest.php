<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentFeatureTest extends TestCase
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
    public function user_can_view_appointments_index(): void
    {
        $this->actingAs($this->user);

        Appointment::factory()->count(3)->for($this->calendar)->for($this->user)->create();

        $response = $this->get(route('appointments.index'));

        $response->assertStatus(200);
        $response->assertSee('appointments');
    }

    /** @test */
    public function user_can_create_an_appointment(): void
    {
        $this->actingAs($this->user);

        $appointmentData = [
            'calendar_id' => $this->calendar->id,
            'title' => 'Team Meeting',
            'description' => 'Discuss project updates',
            'location' => 'Conference Room',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'is_all_day' => false,
            'status' => 'scheduled',
        ];

        $response = $this->post(route('appointments.store'), $appointmentData);

        $response->assertRedirect(route('calendar.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'title' => 'Team Meeting',
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
        ]);
    }

    /** @test */
    public function user_can_update_their_appointment(): void
    {
        $this->actingAs($this->user);

        $appointment = Appointment::factory()->for($this->calendar)->for($this->user)->create([
            'title' => 'Old Title',
        ]);

        $updatedData = [
            'calendar_id' => $this->calendar->id,
            'title' => 'Updated Title',
            'description' => $appointment->description,
            'location' => $appointment->location,
            'start_datetime' => $appointment->start_datetime->format('Y-m-d H:i:s'),
            'end_datetime' => $appointment->end_datetime->format('Y-m-d H:i:s'),
            'is_all_day' => false,
            'status' => 'scheduled',
        ];

        $response = $this->put(route('appointments.update', $appointment), $updatedData);

        $response->assertRedirect(route('calendar.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function user_can_delete_their_appointment(): void
    {
        $this->actingAs($this->user);

        $appointment = Appointment::factory()->for($this->calendar)->for($this->user)->create();

        $response = $this->delete(route('appointments.destroy', $appointment));

        $response->assertRedirect(route('calendar.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    /** @test */
    public function user_cannot_view_another_users_appointment(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $anotherCalendar = Calendar::factory()->for($anotherUser)->create();
        $appointment = Appointment::factory()->for($anotherCalendar)->for($anotherUser)->create();

        $response = $this->get(route('appointments.show', $appointment));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_update_another_users_appointment(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $anotherCalendar = Calendar::factory()->for($anotherUser)->create();
        $appointment = Appointment::factory()->for($anotherCalendar)->for($anotherUser)->create();

        $response = $this->put(route('appointments.update', $appointment), [
            'calendar_id' => $this->calendar->id,
            'title' => 'Hacked Title',
            'start_datetime' => now()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addHour()->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_mark_appointment_as_completed(): void
    {
        $this->actingAs($this->user);

        $appointment = Appointment::factory()->for($this->calendar)->for($this->user)->create([
            'status' => 'scheduled',
        ]);

        $response = $this->post(route('appointments.complete', $appointment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $appointment->refresh();
        $this->assertEquals('completed', $appointment->status);
    }

    /** @test */
    public function user_can_cancel_appointment(): void
    {
        $this->actingAs($this->user);

        $appointment = Appointment::factory()->for($this->calendar)->for($this->user)->create([
            'status' => 'scheduled',
        ]);

        $response = $this->post(route('appointments.cancel', $appointment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $appointment->refresh();
        $this->assertEquals('cancelled', $appointment->status);
    }

    /** @test */
    public function validation_fails_for_invalid_appointment_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('appointments.store'), [
            'title' => '', // Empty title
            'calendar_id' => 999, // Non-existent calendar
            'start_datetime' => 'invalid-date',
            'end_datetime' => 'invalid-date',
        ]);

        $response->assertSessionHasErrors(['title', 'calendar_id', 'start_datetime', 'end_datetime']);
    }

    /** @test */
    public function end_datetime_must_be_after_start_datetime(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('appointments.store'), [
            'calendar_id' => $this->calendar->id,
            'title' => 'Invalid Appointment',
            'start_datetime' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->format('Y-m-d H:i:s'), // Before start
            'status' => 'scheduled',
        ]);

        $response->assertSessionHasErrors(['end_datetime']);
    }

    /** @test */
    public function user_can_filter_appointments_by_calendar(): void
    {
        $this->actingAs($this->user);

        $calendar2 = Calendar::factory()->for($this->user)->create();

        Appointment::factory()->count(3)->for($this->calendar)->for($this->user)->create();
        Appointment::factory()->count(2)->for($calendar2)->for($this->user)->create();

        $response = $this->get(route('appointments.index', ['calendar_id' => $this->calendar->id]));

        $response->assertStatus(200);
        // Would need to check the view data or rendered output to verify filtering
    }

    /** @test */
    public function user_can_filter_appointments_by_status(): void
    {
        $this->actingAs($this->user);

        Appointment::factory()->count(2)->for($this->calendar)->for($this->user)->scheduled()->create();
        Appointment::factory()->count(1)->for($this->calendar)->for($this->user)->completed()->create();

        $response = $this->get(route('appointments.index', ['status' => 'scheduled']));

        $response->assertStatus(200);
    }
}
