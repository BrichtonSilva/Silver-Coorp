<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->sentence(4),
            'responsible' => fake()->optional()->name(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'is_done' => fake()->boolean(30),
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => ['is_done' => true]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'is_done' => false,
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
