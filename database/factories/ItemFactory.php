<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        $catalog = fake()->randomElement([
            ['name' => 'Kopi Arabica 250g', 'price' => 85000],
            ['name' => 'Kopi Robusta 250g', 'price' => 65000],
            ['name' => 'Cold Brew Bottle', 'price' => 35000],
            ['name' => 'Vanilla Syrup 750ml', 'price' => 89000],
            ['name' => 'Paper Cup 12oz Pack', 'price' => 46000],
            ['name' => 'Reusable Tumbler 350ml', 'price' => 125000],
            ['name' => 'French Press 600ml', 'price' => 185000],
        ]);

        return [
            'code' => strtoupper(fake()->bothify('ITM-####')),
            'name' => $catalog['name'],
            'price' => $catalog['price'],
            'stock' => fake()->numberBetween(50, 200),
        ];
    }
}
