<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Salle des fêtes',
                'Centre de congrès',
                'Espace rooftop',
                'Théâtre municipal',
                'Salle de réunion A',
                'Jardin botanique',
                'Stade annexe',
                'Salle polyvalente',
                'Hôtel du Parc',
                'Villa des Arts',
            ]) . ' ' . fake()->city(),
            'address' => fake()->optional(0.8)->address(),
            'capacity' => fake()->numberBetween(10, 5000),
            'type' => fake()->randomElement(Venue::TYPES),
            'rental_price' => fake()->randomFloat(2, 0, 5000),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Set the venue as interne.
     */
    public function interne(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Venue::TYPE_INTERNE,
        ]);
    }

    /**
     * Set the venue as externe.
     */
    public function externe(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Venue::TYPE_EXTERNE,
        ]);
    }
}
