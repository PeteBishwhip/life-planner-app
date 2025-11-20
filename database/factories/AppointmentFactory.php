<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+3 months');
        $duration = $this->faker->randomElement([30, 60, 90, 120]); // minutes
        $endDate = (clone $startDate)->modify("+{$duration} minutes");

        return [
            'calendar_id' => Calendar::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'location' => $this->faker->optional(0.6)->address(),
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'is_all_day' => false,
            'color' => $this->faker->hexColor(),
            'recurrence_rule' => null,
            'recurrence_parent_id' => null,
            'status' => 'scheduled',
        ];
    }

    /**
     * Indicate that the appointment is all day.
     */
    public function allDay(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('now', '+3 months');

            return [
                'start_datetime' => (clone $date)->setTime(0, 0, 0),
                'end_datetime' => (clone $date)->setTime(23, 59, 59),
                'is_all_day' => true,
            ];
        });
    }

    /**
     * Indicate that the appointment is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-3 months', 'now');
            $duration = $this->faker->randomElement([30, 60, 90, 120]);
            $endDate = (clone $startDate)->modify("+{$duration} minutes");

            return [
                'status' => 'completed',
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the appointment is in the past.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-3 months', '-1 day');
            $duration = $this->faker->randomElement([30, 60, 90, 120]);
            $endDate = (clone $startDate)->modify("+{$duration} minutes");

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the appointment is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('+1 hour', '+3 months');
            $duration = $this->faker->randomElement([30, 60, 90, 120]);
            $endDate = (clone $startDate)->modify("+{$duration} minutes");

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
                'status' => 'scheduled',
            ];
        });
    }

    /**
     * Indicate that the appointment is today.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = $this->faker->time('H:i:s');
            $startDate = now()->setTimeFromTimeString($startTime);
            $duration = $this->faker->randomElement([30, 60, 90, 120]);
            $endDate = (clone $startDate)->addMinutes($duration);

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
                'status' => 'scheduled',
            ];
        });
    }

    /**
     * Indicate that the appointment is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'recurrence_rule' => [
                'freq' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
                'interval' => 1,
                'count' => $this->faker->numberBetween(5, 20),
            ],
        ]);
    }

    /**
     * Indicate that the appointment is a meeting.
     */
    public function meeting(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Meeting: ' . $this->faker->sentence(2),
            'location' => $this->faker->randomElement([
                'Conference Room A',
                'Conference Room B',
                'Zoom',
                'Google Meet',
                'Office',
            ]),
        ]);
    }
}
