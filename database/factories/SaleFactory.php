<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100000, 1000000);

        return [
            'code' => 'SL-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'sale_date' => fake()->dateTimeBetween('-6 months'),
            'status' => SaleStatus::Unpaid,
            'subtotal' => $subtotal,
            'paid_amount' => 0,
            'remaining_amount' => $subtotal,
        ];
    }
}
