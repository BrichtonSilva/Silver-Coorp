<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(Provider::CATEGORIES);

        return [
            'name' => match ($category) {
                Provider::CATEGORY_TRAITEUR => fake()->company() . ' Traiteur',
                Provider::CATEGORY_SONORISATION => fake()->company() . ' Son',
                Provider::CATEGORY_DECORATION => fake()->company() . ' Déco',
                Provider::CATEGORY_PHOTOGRAPHE => fake()->name() . ' Photographie',
                default => fake()->company(),
            },
            'category' => $category,
            'contact' => fake()->optional(0.7)->name(),
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'email' => fake()->optional(0.8)->safeEmail(),
            'indicative_price' => fake()->optional(0.6)->randomFloat(2, 100, 5000),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Set the provider category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
