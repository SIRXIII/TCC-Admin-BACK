<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Traveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{

    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id'  => Partner::inRandomOrder()->first()->id ?? Partner::factory()->create()->id,
            'traveler_id' => Traveler::inRandomOrder()->first()->id ?? Traveler::factory()->create()->id,
            'rider_id'    => Rider::inRandomOrder()->first()->id ?? Rider::factory()->create()->id,
            'total_price' => 0,
            'status'      => $this->faker->randomElement(['pending','confirmed','shipped','delivered']),
        ];
    }
}
