<?php

namespace Database\Seeders;

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoSalesSeeder extends Seeder
{
    private const SALE_PREFIX = 'SALE-DEMO-';

    private const PAYMENT_PREFIX = 'PAY-DEMO-';

    public function run(): void
    {
        DB::transaction(function (): void {
            Payment::query()
                ->where('code', 'like', self::PAYMENT_PREFIX . '%')
                ->delete();
            Sale::query()
                ->where('code', 'like', self::SALE_PREFIX . '%')
                ->delete();

            $items = Item::query()->orderBy('code')->get();
            $users = User::query()->orderBy('id')->get();

            if ($items->isEmpty() || $users->isEmpty()) {
                return;
            }

            $today = CarbonImmutable::today();

            for ($index = 1; $index <= 72; $index++) {
                $saleDate = $today->subDays(($index * 3) + ($index % 5));
                $sale = Sale::query()->create([
                    'code' => self::SALE_PREFIX . $saleDate->format('Ymd') . '-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                    'sale_date' => $saleDate->toDateString(),
                    'status' => SaleStatus::Unpaid,
                    'created_by' => $users[($index - 1) % $users->count()]->id,
                ]);

                $subtotal = $this->createSaleItems($sale, $items, $index);
                $paidAmount = $this->createPayments($sale, $subtotal, $saleDate, $index, $users);

                $sale->update([
                    'subtotal' => $subtotal,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $subtotal - $paidAmount,
                    'status' => $this->statusFor($subtotal, $paidAmount),
                ]);
            }
        });
    }

    /**
     * @param  Collection<int, Item>  $items
     */
    private function createSaleItems(Sale $sale, Collection $items, int $saleIndex): float
    {
        $lineCount = 1 + ($saleIndex % 4);
        $subtotal = 0.0;

        for ($line = 0; $line < $lineCount; $line++) {
            $item = $items[($saleIndex + ($line * 5)) % $items->count()];
            $qty = $this->quantityFor($saleIndex, $line);
            $price = (float) $item->price;
            $total = $qty * $price;

            $sale->items()->create([
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'qty' => $qty,
                'price' => $price,
                'total_price' => $total,
            ]);

            $subtotal += $total;
        }

        return $subtotal;
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function createPayments(
        Sale $sale,
        float $subtotal,
        CarbonImmutable $saleDate,
        int $saleIndex,
        Collection $users,
    ): float {
        if ($saleIndex % 7 === 0) {
            return 0.0;
        }

        if ($saleIndex % 5 === 0) {
            $firstPayment = $this->roundRupiah($subtotal * 0.35);
            $this->createPayment($sale, $firstPayment, $saleDate->addDays(2), $saleIndex, 1, $users);

            return $firstPayment;
        }

        if ($saleIndex % 4 === 0) {
            $firstPayment = $this->roundRupiah($subtotal * 0.45);
            $secondPayment = $subtotal - $firstPayment;
            $this->createPayment($sale, $firstPayment, $saleDate->addDays(1), $saleIndex, 1, $users);
            $this->createPayment($sale, $secondPayment, $saleDate->addDays(8), $saleIndex, 2, $users);

            return $subtotal;
        }

        $this->createPayment($sale, $subtotal, $saleDate->addDays(1 + ($saleIndex % 3)), $saleIndex, 1, $users);

        return $subtotal;
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function createPayment(
        Sale $sale,
        float $amount,
        CarbonImmutable $paymentDate,
        int $saleIndex,
        int $sequence,
        Collection $users,
    ): Payment {
        return Payment::query()->create([
            'code' => self::PAYMENT_PREFIX
                . $paymentDate->format('Ymd')
                . '-'
                . str_pad((string) $saleIndex, 3, '0', STR_PAD_LEFT)
                . '-'
                . $sequence,
            'sale_id' => $sale->id,
            'payment_date' => $paymentDate->toDateString(),
            'amount' => $amount,
            'note' => $sequence > 1 ? 'Pelunasan termin ke-' . $sequence : 'Pembayaran kasir',
            'created_by' => $users[($saleIndex + $sequence) % $users->count()]->id,
        ]);
    }

    private function quantityFor(int $saleIndex, int $line): int
    {
        return match (($saleIndex + $line) % 6) {
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 4,
            4 => 6,
            default => 8,
        };
    }

    private function roundRupiah(float $amount): float
    {
        return floor($amount / 1000) * 1000;
    }

    private function statusFor(float $subtotal, float $paidAmount): SaleStatus
    {
        return match (true) {
            $paidAmount <= 0 => SaleStatus::Unpaid,
            $paidAmount < $subtotal => SaleStatus::PartiallyPaid,
            default => SaleStatus::Paid,
        };
    }
}
