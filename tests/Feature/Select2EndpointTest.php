<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Select2EndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $this->actingAs($admin);
    }

    public function test_item_select2_endpoint_searches_and_paginates_options(): void
    {
        Item::factory()->create([
            'code' => 'ABC-001',
            'name' => 'Kopi Pilihan',
            'price' => 125000,
        ]);
        Item::factory()->create([
            'code' => 'XYZ-001',
            'name' => 'Produk Lain',
            'price' => 50000,
        ]);

        $this->getJson(route('items.select2', ['term' => 'ABC']))
            ->assertOk()
            ->assertJsonPath('results.0.text', 'ABC-001 - Kopi Pilihan')
            ->assertJsonPath('results.0.price', 125000)
            ->assertJsonPath('pagination.more', false)
            ->assertJsonCount(1, 'results');
    }

    public function test_sale_select2_endpoint_only_returns_payable_sales(): void
    {
        Sale::factory()->create([
            'code' => 'SL-OPEN-001',
            'status' => SaleStatus::Unpaid,
            'subtotal' => 200000,
            'paid_amount' => 50000,
            'remaining_amount' => 150000,
        ]);
        Sale::factory()->create([
            'code' => 'SL-PAID-001',
            'status' => SaleStatus::Paid,
            'subtotal' => 200000,
            'paid_amount' => 200000,
            'remaining_amount' => 0,
        ]);

        $this->getJson(route('sales.select2', ['term' => 'SL-']))
            ->assertOk()
            ->assertJsonPath('results.0.text', 'SL-OPEN-001 - Sisa Rp 150.000')
            ->assertJsonPath('results.0.remaining_amount', 150000)
            ->assertJsonPath('pagination.more', false)
            ->assertJsonCount(1, 'results');
    }
}
