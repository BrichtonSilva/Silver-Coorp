<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventProviderAssignment;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventProviderAssignmentFactory extends Factory
{
    protected $model = EventProviderAssignment::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'provider_id' => Provider::factory(),
            'cost' => fake()->randomFloat(2, 100, 5000),
            'status' => fake()->randomElement(EventProviderAssignment::STATUSES),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => EventProviderAssignment::STATUS_CONFIRME]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => EventProviderAssignment::STATUS_ANNULE]);
    }
}
