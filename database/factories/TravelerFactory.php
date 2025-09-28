<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Traveler>
 */
class TravelerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_photo' => fake()->imageUrl(200, 200, 'people'),
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'country' => fake()->country(),
            'address' => fake()->streetAddress(),
            'spent_amount' => fake()->randomFloat(2, 0, 10000),
            'status' => fake()->randomElement(['active', 'suspended']),
            'last_active' => fake()->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
