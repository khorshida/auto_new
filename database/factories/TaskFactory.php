<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'notes' => null,
            'due_on' => null,
            'status' => TaskStatus::Open,
            'inquiry_id' => null,
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
        ];
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Done,
        ]);
    }
}
