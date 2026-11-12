<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 18);
        $endHour = $startHour + fake()->numberBetween(1, 4);

        return [
            'client_id' => Client::factory(),
            'type' => fake()->randomElement(Event::TYPES),
            'date' => fake()->dateTimeBetween('+1 day', '+6 months')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $endHour),
            'venue_id' => Venue::factory(['capacity' => 500]), // always above guest_count (max 200)
            'guest_count' => fake()->numberBetween(10, 200),
            'budget' => fake()->randomFloat(2, 500, 50000),
            'status' => fake()->randomElement(Event::STATUSES),
            'cancellation_reason' => null,
            'charged_user_id' => User::factory()->charge(),
        ];
    }

    /**
     * Set the event as a draft.
     */
    public function brouillon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_BROUILLON,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Set the event as confirmed.
     */
    public function confirme(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_CONFIRME,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Set the event as terminated.
     */
    public function termine(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_TERMINE,
            'date' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Set the event as cancelled.
     */
    public function annule(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Event::STATUS_ANNULE,
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
