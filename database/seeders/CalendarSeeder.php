<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users or create a test user
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        foreach ($users as $user) {
            // Create a personal calendar (default)
            Calendar::factory()
                ->personal()
                ->default()
                ->for($user)
                ->create([
                    'name' => 'Personal Calendar',
                    'description' => 'Personal events and appointments',
                ]);

            // Create a business calendar
            Calendar::factory()
                ->business()
                ->for($user)
                ->create([
                    'name' => 'Business Calendar',
                    'description' => 'Work-related meetings and appointments',
                ]);

            // Create a custom calendar
            Calendar::factory()
                ->custom()
                ->for($user)
                ->create([
                    'name' => 'Family Calendar',
                    'description' => 'Family events and gatherings',
                    'color' => '#EC4899',
                ]);
        }
    }
}
