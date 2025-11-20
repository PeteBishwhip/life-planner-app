<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_calendar(): void
    {
        $calendar = Calendar::factory()->for($this->user)->create([
            'name' => 'My Calendar',
            'type' => 'personal',
        ]);

        $this->assertDatabaseHas('calendars', [
            'name' => 'My Calendar',
            'type' => 'personal',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user(): void
    {
        $calendar = Calendar::factory()->for($this->user)->create();

        $this->assertInstanceOf(User::class, $calendar->user);
        $this->assertEquals($this->user->id, $calendar->user_id);
    }

    /** @test */
    public function it_has_many_appointments(): void
    {
        $calendar = Calendar::factory()->for($this->user)->create();

        Appointment::factory()
            ->count(3)
            ->for($calendar)
            ->for($this->user)
            ->create();

        $this->assertCount(3, $calendar->appointments);
        $this->assertInstanceOf(Appointment::class, $calendar->appointments->first());
    }

    /** @test */
    public function it_casts_boolean_attributes_correctly(): void
    {
        $calendar = Calendar::factory()->for($this->user)->create([
            'is_visible' => true,
            'is_default' => false,
        ]);

        $this->assertTrue($calendar->is_visible);
        $this->assertFalse($calendar->is_default);
        $this->assertIsBool($calendar->is_visible);
        $this->assertIsBool($calendar->is_default);
    }

    /** @test */
    public function it_sets_default_color_based_on_type_if_not_provided(): void
    {
        $personalCalendar = Calendar::factory()->for($this->user)->create([
            'type' => 'personal',
            'color' => null,
        ]);

        $businessCalendar = Calendar::factory()->for($this->user)->create([
            'type' => 'business',
            'color' => null,
        ]);

        $customCalendar = Calendar::factory()->for($this->user)->create([
            'type' => 'custom',
            'color' => null,
        ]);

        $this->assertEquals('#3B82F6', $personalCalendar->color);
        $this->assertEquals('#10B981', $businessCalendar->color);
        $this->assertEquals('#8B5CF6', $customCalendar->color);
    }

    /** @test */
    public function it_ensures_only_one_default_calendar_per_user(): void
    {
        $calendar1 = Calendar::factory()->for($this->user)->create(['is_default' => true]);
        $calendar2 = Calendar::factory()->for($this->user)->create(['is_default' => false]);

        // Make calendar2 default
        $calendar2->update(['is_default' => true]);

        // Refresh calendar1 from database
        $calendar1->refresh();

        $this->assertFalse($calendar1->is_default);
        $this->assertTrue($calendar2->is_default);
    }

    /** @test */
    public function scope_visible_filters_visible_calendars(): void
    {
        Calendar::factory()->for($this->user)->create(['is_visible' => true]);
        Calendar::factory()->for($this->user)->create(['is_visible' => true]);
        Calendar::factory()->for($this->user)->create(['is_visible' => false]);

        $visibleCalendars = Calendar::visible()->get();

        $this->assertCount(2, $visibleCalendars);
    }

    /** @test */
    public function scope_for_user_filters_by_user_id(): void
    {
        $anotherUser = User::factory()->create();

        Calendar::factory()->count(2)->for($this->user)->create();
        Calendar::factory()->count(3)->for($anotherUser)->create();

        $userCalendars = Calendar::forUser($this->user->id)->get();

        $this->assertCount(2, $userCalendars);
        $this->assertTrue($userCalendars->every(fn($cal) => $cal->user_id === $this->user->id));
    }

    /** @test */
    public function scope_of_type_filters_by_calendar_type(): void
    {
        Calendar::factory()->for($this->user)->create(['type' => 'personal']);
        Calendar::factory()->for($this->user)->create(['type' => 'business']);
        Calendar::factory()->for($this->user)->create(['type' => 'personal']);

        $personalCalendars = Calendar::ofType('personal')->get();

        $this->assertCount(2, $personalCalendars);
        $this->assertTrue($personalCalendars->every(fn($cal) => $cal->type === 'personal'));
    }

    /** @test */
    public function scope_default_filters_default_calendars(): void
    {
        Calendar::factory()->for($this->user)->create(['is_default' => true]);
        Calendar::factory()->for($this->user)->create(['is_default' => false]);
        Calendar::factory()->for($this->user)->create(['is_default' => false]);

        $defaultCalendars = Calendar::default()->get();

        $this->assertCount(1, $defaultCalendars);
        $this->assertTrue($defaultCalendars->first()->is_default);
    }

    /** @test */
    public function it_can_combine_multiple_scopes(): void
    {
        Calendar::factory()->for($this->user)->create([
            'type' => 'personal',
            'is_visible' => true,
        ]);

        Calendar::factory()->for($this->user)->create([
            'type' => 'personal',
            'is_visible' => false,
        ]);

        Calendar::factory()->for($this->user)->create([
            'type' => 'business',
            'is_visible' => true,
        ]);

        $results = Calendar::forUser($this->user->id)
            ->ofType('personal')
            ->visible()
            ->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function validation_rules_require_name(): void
    {
        $rules = Calendar::rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertContains('required', $rules['name']);
    }

    /** @test */
    public function validation_rules_require_valid_type(): void
    {
        $rules = Calendar::rules();

        $this->assertArrayHasKey('type', $rules);
        $this->assertContains('required', $rules['type']);

        // Check that type validation includes the correct enum values
        $typeRule = collect($rules['type'])->first(fn($rule) => str_contains($rule, 'in:'));
        $this->assertStringContainsString('personal', $typeRule);
        $this->assertStringContainsString('business', $typeRule);
        $this->assertStringContainsString('custom', $typeRule);
    }

    /** @test */
    public function validation_rules_require_valid_color_format(): void
    {
        $rules = Calendar::rules();

        $this->assertArrayHasKey('color', $rules);
        $this->assertContains('required', $rules['color']);

        // Check that color validation includes regex
        $hasRegex = collect($rules['color'])->contains(fn($rule) => str_contains($rule, 'regex'));
        $this->assertTrue($hasRegex);
    }
}
