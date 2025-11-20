<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_view_calendars_index(): void
    {
        $this->actingAs($this->user);

        Calendar::factory()->count(3)->for($this->user)->create();

        $response = $this->get(route('calendars.index'));

        $response->assertStatus(200);
        $response->assertSee('calendars');
    }

    /** @test */
    public function user_can_create_a_calendar(): void
    {
        $this->actingAs($this->user);

        $calendarData = [
            'name' => 'My New Calendar',
            'type' => 'personal',
            'color' => '#3B82F6',
            'is_visible' => true,
            'is_default' => false,
            'description' => 'A test calendar',
        ];

        $response = $this->post(route('calendars.store'), $calendarData);

        $response->assertRedirect(route('calendars.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calendars', [
            'name' => 'My New Calendar',
            'type' => 'personal',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function user_can_update_their_calendar(): void
    {
        $this->actingAs($this->user);

        $calendar = Calendar::factory()->for($this->user)->create([
            'name' => 'Old Name',
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'type' => $calendar->type,
            'color' => $calendar->color,
            'is_visible' => true,
            'is_default' => false,
        ];

        $response = $this->put(route('calendars.update', $calendar), $updatedData);

        $response->assertRedirect(route('calendars.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function user_can_delete_their_non_default_calendar(): void
    {
        $this->actingAs($this->user);

        $calendar = Calendar::factory()->for($this->user)->create([
            'is_default' => false,
        ]);

        $response = $this->delete(route('calendars.destroy', $calendar));

        $response->assertRedirect(route('calendars.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('calendars', [
            'id' => $calendar->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_default_calendar(): void
    {
        $this->actingAs($this->user);

        $calendar = Calendar::factory()->for($this->user)->create([
            'is_default' => true,
        ]);

        $response = $this->delete(route('calendars.destroy', $calendar));

        $response->assertRedirect(route('calendars.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
        ]);
    }

    /** @test */
    public function user_cannot_view_another_users_calendar(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $calendar = Calendar::factory()->for($anotherUser)->create();

        $response = $this->get(route('calendars.show', $calendar));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_update_another_users_calendar(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $calendar = Calendar::factory()->for($anotherUser)->create();

        $response = $this->put(route('calendars.update', $calendar), [
            'name' => 'Hacked Name',
            'type' => 'personal',
            'color' => '#FF0000',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_toggle_calendar_visibility(): void
    {
        $this->actingAs($this->user);

        $calendar = Calendar::factory()->for($this->user)->create([
            'is_visible' => true,
        ]);

        $response = $this->post(route('calendars.toggle-visibility', $calendar));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $calendar->refresh();
        $this->assertFalse($calendar->is_visible);
    }

    /** @test */
    public function user_can_set_calendar_as_default(): void
    {
        $this->actingAs($this->user);

        $calendar1 = Calendar::factory()->for($this->user)->create(['is_default' => true]);
        $calendar2 = Calendar::factory()->for($this->user)->create(['is_default' => false]);

        $response = $this->post(route('calendars.set-default', $calendar2));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $calendar1->refresh();
        $calendar2->refresh();

        $this->assertFalse($calendar1->is_default);
        $this->assertTrue($calendar2->is_default);
    }

    /** @test */
    public function validation_fails_for_invalid_calendar_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('calendars.store'), [
            'name' => '', // Empty name
            'type' => 'invalid_type',
            'color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'color']);
    }
}
