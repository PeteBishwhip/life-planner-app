<?php

namespace Database\Factories;

use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportLogFactory extends Factory
{
    protected $model = ImportLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'filename' => fake()->word().'.ics',
            'import_type' => fake()->randomElement(['ics', 'csv', 'google', 'outlook']),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'completed_with_errors', 'failed']),
            'records_imported' => fake()->numberBetween(0, 100),
            'records_failed' => fake()->numberBetween(0, 10),
            'error_log' => [],
        ];
    }
}
