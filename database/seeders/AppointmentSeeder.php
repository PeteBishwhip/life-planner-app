<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Calendar;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $calendars = Calendar::all();

        if ($calendars->isEmpty()) {
            $this->command->warn('No calendars found. Please run CalendarSeeder first.');

            return;
        }

        foreach ($calendars as $calendar) {
            // Create upcoming appointments
            Appointment::factory()
                ->count(5)
                ->upcoming()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create today's appointments
            Appointment::factory()
                ->count(2)
                ->today()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create past appointments
            Appointment::factory()
                ->count(3)
                ->past()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create all-day events
            Appointment::factory()
                ->count(2)
                ->allDay()
                ->upcoming()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create meetings
            Appointment::factory()
                ->count(3)
                ->meeting()
                ->upcoming()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create completed appointments
            Appointment::factory()
                ->count(2)
                ->completed()
                ->for($calendar)
                ->for($calendar->user)
                ->create();

            // Create cancelled appointments
            Appointment::factory()
                ->cancelled()
                ->for($calendar)
                ->for($calendar->user)
                ->create();
        }
    }
}
