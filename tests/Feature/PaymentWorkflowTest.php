<?php

namespace Tests\Feature;

use App\Contracts\SaleRepositoryInterface;
use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\ItemService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_and_full_payments_recalculate_sale_status(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000]);
        $sales = app(SaleRepositoryInterface::class);
        $payments = app(PaymentService::class);

        $sale = $sales->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $user->id);

        $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => now()->toDateString(),
            'amount' => 50000,
        ], $user->id);

        $this->assertSame(SaleStatus::PartiallyPaid, $sale->refresh()->status);
        $this->assertEquals(50000, (float) $sale->paid_amount);
        $this->assertEquals(150000, (float) $sale->remaining_amount);

        $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => now()->toDateString(),
            'amount' => 150000,
        ], $user->id);

        $this->assertSame(SaleStatus::Paid, $sale->refresh()->status);
        $this->assertEquals(200000, (float) $sale->paid_amount);
        $this->assertEquals(0, (float) $sale->remaining_amount);
    }

    public function test_sale_creation_reduces_item_stock_regardless_of_payment_status(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000, 'stock' => 5]);
        $sales = app(SaleRepositoryInterface::class);

        $sale = $sales->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $user->id);

        $this->assertSame(SaleStatus::Unpaid, $sale->status);
        $this->assertSame(3, $item->refresh()->stock);
    }

    public function test_sale_creation_cannot_reduce_item_stock_below_zero(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000, 'stock' => 1]);
        $sales = app(SaleRepositoryInterface::class);

        try {
            $sales->createWithItems([
                'sale_date' => now()->toDateString(),
                'items' => [
                    ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
                ],
            ], $user->id);

            $this->fail('Expected stock validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame(1, $item->refresh()->stock);
            $this->assertSame(0, Sale::query()->count());
            $this->assertArrayHasKey('items', $exception->errors());
        }
    }

    public function test_sale_update_adjusts_item_stock_by_quantity_difference(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000, 'stock' => 10]);
        $sales = app(SaleRepositoryInterface::class);

        $sale = $sales->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $user->id);

        $this->assertSame(8, $item->refresh()->stock);

        $sales->updateWithItems($sale->refresh(), [
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 4, 'price' => 100000],
            ],
        ]);

        $this->assertSame(6, $item->refresh()->stock);

        $sales->updateWithItems($sale->refresh(), [
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 1, 'price' => 100000],
            ],
        ]);

        $this->assertSame(9, $item->refresh()->stock);
    }

    public function test_sale_delete_restores_item_stock(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000, 'stock' => 5]);
        $sales = app(SaleRepositoryInterface::class);

        $sale = $sales->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $user->id);

        $this->assertSame(3, $item->refresh()->stock);

        $sales->delete($sale->refresh());

        $this->assertSame(5, $item->refresh()->stock);
    }

    public function test_payment_cannot_exceed_sale_total(): void
    {
        $user = User::factory()->create();
        $sale = Sale::factory()->create(['subtotal' => 100000, 'remaining_amount' => 100000]);
        $payments = app(PaymentService::class);

        $this->expectException(ValidationException::class);

        $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => now()->toDateString(),
            'amount' => 100001,
        ], $user->id);
    }

    public function test_deleting_payment_reopens_sale_status(): void
    {
        $user = User::factory()->create();
        $sale = Sale::factory()->create([
            'status' => SaleStatus::Paid,
            'subtotal' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
        ]);
        $payment = Payment::factory()->create([
            'sale_id' => $sale->id,
            'amount' => 100000,
            'created_by' => $user->id,
        ]);

        app(PaymentService::class)->delete($payment);

        $this->assertSame(SaleStatus::Unpaid, $sale->refresh()->status);
        $this->assertEquals(0, (float) $sale->paid_amount);
        $this->assertEquals(100000, (float) $sale->remaining_amount);
    }

    public function test_paid_sale_cannot_be_changed_or_deleted(): void
    {
        $sale = Sale::factory()->create(['status' => SaleStatus::Paid]);
        $sales = app(SaleRepositoryInterface::class);

        $this->expectException(RuntimeException::class);
        $sales->delete($sale);
    }

    public function test_partially_paid_sale_cannot_be_reduced_below_paid_amount(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000]);
        $sales = app(SaleRepositoryInterface::class);
        $payments = app(PaymentService::class);

        $sale = $sales->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $user->id);

        $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => now()->toDateString(),
            'amount' => 150000,
        ], $user->id);

        $this->expectException(ValidationException::class);

        $sales->updateWithItems($sale->refresh(), [
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $item->id, 'qty' => 1, 'price' => 100000],
            ],
        ]);
    }

    public function test_backdated_sales_receive_unique_codes(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['price' => 100000]);
        $sales = app(SaleRepositoryInterface::class);
        $saleDate = now()->subMonth()->toDateString();

        $firstSale = $sales->createWithItems([
            'sale_date' => $saleDate,
            'items' => [
                ['item_id' => $item->id, 'qty' => 1, 'price' => 100000],
            ],
        ], $user->id);

        $secondSale = $sales->createWithItems([
            'sale_date' => $saleDate,
            'items' => [
                ['item_id' => $item->id, 'qty' => 1, 'price' => 100000],
            ],
        ], $user->id);

        $this->assertNotSame($firstSale->code, $secondSale->code);
        $this->assertStringStartsWith('SL-' . now()->subMonth()->format('Ymd'), $firstSale->code);
        $this->assertStringStartsWith('SL-' . now()->subMonth()->format('Ymd'), $secondSale->code);
    }

    public function test_backdated_payments_receive_unique_codes(): void
    {
        $user = User::factory()->create();
        $sale = Sale::factory()->create(['subtotal' => 100000, 'remaining_amount' => 100000]);
        $payments = app(PaymentService::class);
        $paymentDate = now()->subMonth()->toDateString();

        $firstPayment = $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => $paymentDate,
            'amount' => 10000,
        ], $user->id);

        $secondPayment = $payments->create([
            'sale_id' => $sale->id,
            'payment_date' => $paymentDate,
            'amount' => 10000,
        ], $user->id);

        $this->assertNotSame($firstPayment->code, $secondPayment->code);
        $this->assertStringStartsWith('PY-' . now()->subMonth()->format('Ymd'), $firstPayment->code);
        $this->assertStringStartsWith('PY-' . now()->subMonth()->format('Ymd'), $secondPayment->code);
    }

    public function test_item_used_by_sale_cannot_be_deleted(): void
    {
        $item = Item::factory()->create(['price' => 100000]);
        $sale = Sale::factory()->create(['subtotal' => 100000, 'remaining_amount' => 100000]);

        $sale->items()->create([
            'item_id' => $item->id,
            'item_code' => $item->code,
            'item_name' => $item->name,
            'qty' => 1,
            'price' => 100000,
            'total_price' => 100000,
        ]);

        $this->expectException(RuntimeException::class);

        app(ItemService::class)->delete($item);
    }
}
