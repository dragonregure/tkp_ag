<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    public function definition(): array
    {
        $item = Item::query()->inRandomOrder()->first() ?? Item::factory()->create();
        $qty = fake()->numberBetween(1, 6);
        $price = (float) $item->price;

        return [
            'sale_id' => Sale::factory(),
            'item_id' => $item->id,
            'item_code' => $item->code,
            'item_name' => $item->name,
            'qty' => $qty,
            'price' => $price,
            'total_price' => $qty * $price,
        ];
    }
}
