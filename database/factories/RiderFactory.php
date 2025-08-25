<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rider>
 */
class RiderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
            'rider_id' => 'RID-' . $this->faker->unique()->numberBetween(1, 1000),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->imageUrl(640, 480, 'people'),
            'online' => $this->faker->boolean(),
            'delivered_orders' => $this->faker->numberBetween(0, 100),
            'average_rating' => $this->faker->randomFloat(2, 0, 5),
            'profile_photo' => $this->faker->imageUrl(200, 200, 'rider'),

        ];
    }
}
