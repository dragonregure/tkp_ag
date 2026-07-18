<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'PY-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'payment_date' => fake()->dateTimeBetween('-6 months'),
            'amount' => fake()->numberBetween(10000, 500000),
            'note' => fake()->randomElement([
                'Pembayaran kasir',
                'Transfer bank',
                'Pelunasan invoice',
                'Pembayaran termin pertama',
                'Pembayaran termin kedua',
            ]),
        ];
    }
}
