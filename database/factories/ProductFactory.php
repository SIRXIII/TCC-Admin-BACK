<?php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name'              => $this->faker->word(),
            'brand'             => $this->faker->company(),
            'color'             => $this->faker->safeColorName(),
            'material'          => $this->faker->word(),
            'care_method'       => $this->faker->randomElement(['Dry Clean', 'Hand Wash', 'Machine Wash']),
            'weight'            => $this->faker->randomFloat(2, 0.1, 10) . ' kg',
            'sku'               => strtoupper($this->faker->bothify('SKU###??')),
            'base_price'        => $this->faker->randomFloat(2, 10, 500),
            'deposit'           => $this->faker->randomFloat(2, 5, 100),
            'late_fee'          => $this->faker->randomFloat(2, 5, 50),
            'replacement_value' => $this->faker->randomFloat(2, 100, 1000),
            'buy_price'         => $this->faker->randomFloat(2, 50, 600),
            'prep_buffer'       => $this->faker->numberBetween(1, 48),
            'min_rental'        => $this->faker->numberBetween(1, 5),
            'max_rental'        => $this->faker->numberBetween(5, 30),
            'blackout_date'     => $this->faker->optional()->date(),
            'fit_category'       =>  $this->faker->randomElement(['looser', 'tight', 'fit']),
            'location'          => $this->faker->city(),
            'length_unit'       => $this->faker->randomElement(['cm', 'inch']),
            'length'            => $this->faker->numberBetween(50, 200),
            'chest'             => $this->faker->numberBetween(30, 60),
            'sleeve'            => $this->faker->numberBetween(20, 70),
            'condition_grade'   => $this->faker->randomElement(['A', 'B', 'C']),
            'status'            => $this->faker->randomElement(['active', 'inactive']),
            'note'              => $this->faker->sentence(),
        ];
    }
}
