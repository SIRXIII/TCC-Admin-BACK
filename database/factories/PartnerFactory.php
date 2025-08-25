<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

             return [
            'profile_photo' => $this->faker->imageUrl(200, 200, 'partners'),

            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'category' => $this->faker->randomElement(['Fashion']),
            'location' => $this->faker->city(),
            // 'documents' => $this->faker->filePath(),
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'status' => $this->faker->randomElement(['active', 'suspended']),
            'created_at' => now(),
            'updated_at' => now(),
        ];

    }
}
