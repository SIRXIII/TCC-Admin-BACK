<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Faker\Generator as Faker;

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
        $faker = \Faker\Factory::create();
        
        return [
            'profile_photo' => 'https://via.placeholder.com/200x200/cccccc/ffffff?text=Profile',
            'name' => $faker->name(),
            'username' => $faker->unique()->userName(),
            'email' => $faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => $faker->phoneNumber(),
            'country' => $faker->country(),
            'address' => $faker->streetAddress(),
            'spent_amount' => $faker->randomFloat(2, 0, 10000),
            'status' => $faker->randomElement(['active', 'suspended']),
            'last_active' => $faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
