<?php

namespace Database\Factories;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calendar>
 */
class CalendarFactory extends Factory
{
    protected $model = Calendar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['personal', 'business', 'custom'];
        $type = $this->faker->randomElement($types);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true) . ' Calendar',
            'type' => $type,
            'color' => match($type) {
                'personal' => $this->faker->randomElement(['#3B82F6', '#EF4444', '#F59E0B']),
                'business' => $this->faker->randomElement(['#10B981', '#8B5CF6', '#EC4899']),
                'custom' => $this->faker->randomElement(['#6366F1', '#14B8A6', '#F97316']),
            },
            'is_visible' => $this->faker->boolean(90), // 90% chance of being visible
            'is_default' => false,
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the calendar is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the calendar is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Indicate that the calendar is the default calendar.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the calendar is a personal calendar.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
            'color' => '#3B82F6',
        ]);
    }

    /**
     * Indicate that the calendar is a business calendar.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'business',
            'color' => '#10B981',
        ]);
    }

    /**
     * Indicate that the calendar is a custom calendar.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'custom',
            'color' => '#8B5CF6',
        ]);
    }
}
