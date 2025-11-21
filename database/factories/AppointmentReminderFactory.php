<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppointmentReminder>
 */
class AppointmentReminderFactory extends Factory
{
    protected $model = AppointmentReminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'reminder_minutes_before' => $this->faker->randomElement([5, 15, 30, 60, 1440]), // 5min, 15min, 30min, 1hr, 1day
            'notification_type' => $this->faker->randomElement(['email', 'browser']),
            'is_sent' => false,
            'sent_at' => null,
        ];
    }

    /**
     * Indicate that the reminder has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sent' => true,
            'sent_at' => now()->subMinutes($this->faker->numberBetween(1, 120)),
        ]);
    }

    /**
     * Indicate that the reminder is for email notification.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'email',
        ]);
    }

    /**
     * Indicate that the reminder is for browser notification.
     */
    public function browser(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'browser',
        ]);
    }

    /**
     * Set reminder for 5 minutes before.
     */
    public function fiveMinutes(): static
    {
        return $this->state(fn (array $attributes) => [
            'reminder_minutes_before' => 5,
        ]);
    }

    /**
     * Set reminder for 15 minutes before.
     */
    public function fifteenMinutes(): static
    {
        return $this->state(fn (array $attributes) => [
            'reminder_minutes_before' => 15,
        ]);
    }

    /**
     * Set reminder for 1 day before.
     */
    public function oneDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'reminder_minutes_before' => 1440,
        ]);
    }
}
