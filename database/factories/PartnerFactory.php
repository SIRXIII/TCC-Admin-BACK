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
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

 // Generate random availability
$availability = [];
foreach ($days as $day) {
    $checked = $this->faker->boolean(50);
    if ($checked) {
        $startHour = $this->faker->numberBetween(1, 11);
        $endHour = $this->faker->numberBetween(12, 8);

        $startPeriod = $this->faker->randomElement(['AM', 'PM']);
        $endPeriod = $this->faker->randomElement(['AM', 'PM']);

        $startTime = sprintf('%02d:00 %s', $startHour, $startPeriod);
        $endTime = sprintf('%02d:00 %s', $this->faker->numberBetween($startHour + 1, 12), $endPeriod);
    } else {
        $startTime = '';
        $endTime = '';
    }

    $availability[$day] = [
        'checked' => $checked,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ];
}

    return [
        'profile_photo' => $this->faker->imageUrl(200, 200, 'business'),
        'name' => $this->faker->name(),
        'business_name' => $this->faker->company(),
        'email' => $this->faker->unique()->safeEmail(),
        'phone' => $this->faker->phoneNumber(),
        'category' => $this->faker->randomElement(['Fashion', 'Electronics', 'Food', 'Health']),
        'address' => $this->faker->address(),
        'latitude' => $this->faker->latitude(),
        'longitude' => $this->faker->longitude(),
        'availability' => json_encode($availability), // store as JSON
        'tax_id' => $this->faker->bothify('??######'),
        'username' => $this->faker->unique()->userName(),
        'password' => Hash::make('password'),
        'status' => $this->faker->randomElement(['active', 'suspended', 'pending']),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

}

