<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Client::TYPES);

        return [
            'name' => $type === Client::TYPE_ENTREPRISE
                ? fake()->company()
                : fake()->name(),
            'type' => $type,
            'email' => fake()->optional(0.8)->safeEmail(),
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'address' => fake()->optional(0.6)->address(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Set the client as a particulier.
     */
    public function particulier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_PARTICULIER,
            'name' => fake()->name(),
        ]);
    }

    /**
     * Set the client as an entreprise.
     */
    public function entreprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_ENTREPRISE,
            'name' => fake()->company(),
        ]);
    }
}
