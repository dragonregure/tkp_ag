<?php

namespace Tests\Feature;

use App\Contracts\SaleRepositoryInterface;
use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::ADMIN);

        $this->actingAs($this->admin);
    }

    public function test_dashboard_date_range_filters_widgets_and_charts(): void
    {
        $item = Item::factory()->create(['name' => 'Kopi Filter', 'price' => 100000]);
        $sales = app(SaleRepositoryInterface::class);

        $sales->createWithItems([
            'sale_date' => '2026-01-15',
            'items' => [
                ['item_id' => $item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $this->admin->id);

        $sales->createWithItems([
            'sale_date' => '2026-02-15',
            'items' => [
                ['item_id' => $item->id, 'qty' => 3, 'price' => 100000],
            ],
        ], $this->admin->id);

        $this->get(route('dashboard.index', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]))
            ->assertOk()
            ->assertSee('Jumlah Transaksi')
            ->assertSee('Rp 200.000')
            ->assertSee('Qty Item Terjual')
            ->assertSee('"month":"2026-01"', false)
            ->assertDontSee('"month":"2026-02"', false);
    }

    public function test_sales_datatable_filters_by_sale_date_and_sorts_newest_first(): void
    {
        Sale::factory()->create([
            'code' => 'SL-OLD',
            'sale_date' => '2026-01-10',
        ]);
        Sale::factory()->create([
            'code' => 'SL-NEW',
            'sale_date' => '2026-01-20',
        ]);
        Sale::factory()->create([
            'code' => 'SL-OUTSIDE',
            'sale_date' => '2026-02-10',
        ]);

        $response = $this->getJson(route('sales.data', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.code', 'SL-NEW')
            ->assertJsonPath('data.1.code', 'SL-OLD');
    }

    public function test_payments_datatable_filters_by_payment_date_and_sorts_newest_first(): void
    {
        $sale = Sale::factory()->create([
            'subtotal' => 500000,
            'remaining_amount' => 500000,
        ]);

        Payment::factory()->create([
            'sale_id' => $sale->id,
            'code' => 'PY-OLD',
            'payment_date' => '2026-01-10',
            'amount' => 50000,
        ]);
        Payment::factory()->create([
            'sale_id' => $sale->id,
            'code' => 'PY-NEW',
            'payment_date' => '2026-01-20',
            'amount' => 75000,
        ]);
        Payment::factory()->create([
            'sale_id' => $sale->id,
            'code' => 'PY-OUTSIDE',
            'payment_date' => '2026-02-10',
            'amount' => 25000,
        ]);

        $response = $this->getJson(route('payments.data', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.code', 'PY-NEW')
            ->assertJsonPath('data.1.code', 'PY-OLD');
    }

    public function test_payment_update_cannot_move_payment_to_another_sale(): void
    {
        $originalSale = Sale::factory()->create([
            'subtotal' => 200000,
            'remaining_amount' => 200000,
        ]);
        $otherSale = Sale::factory()->create([
            'subtotal' => 200000,
            'remaining_amount' => 200000,
        ]);

        $payment = app(PaymentService::class)->create([
            'sale_id' => $originalSale->id,
            'payment_date' => '2026-01-10',
            'amount' => 50000,
        ], $this->admin->id);

        $editResponse = $this->get(route('payments.edit', $payment))->assertOk();
        preg_match('/name="_token" value="([^"]+)"/', $editResponse->getContent(), $matches);

        $this->put(route('payments.update', $payment), [
            '_token' => $matches[1],
            'sale_id' => $otherSale->id,
            'payment_date' => '2026-01-11',
            'amount' => 75000,
            'note' => 'Updated amount only',
        ])->assertRedirect(route('payments.show', $payment));

        $payment->refresh();

        $this->assertSame($originalSale->id, $payment->sale_id);
        $this->assertEquals(75000, (float) $payment->amount);
        $this->assertSame(SaleStatus::PartiallyPaid, $originalSale->refresh()->status);
        $this->assertSame(SaleStatus::Unpaid, $otherSale->refresh()->status);
    }
}
