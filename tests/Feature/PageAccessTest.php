<?php

namespace Tests\Feature;

use App\Contracts\SaleRepositoryInterface;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editableUser;

    private Item $item;

    private Sale $sale;

    private Payment $payment;

    private Role $editableRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::ADMIN);
        $this->editableUser = User::factory()->create();
        $this->editableRole = Role::findByName(Roles::STAFF);
        $this->item = Item::factory()->create(['price' => 100000]);

        $this->sale = app(SaleRepositoryInterface::class)->createWithItems([
            'sale_date' => now()->toDateString(),
            'items' => [
                ['item_id' => $this->item->id, 'qty' => 2, 'price' => 100000],
            ],
        ], $this->admin->id);

        $this->payment = app(PaymentService::class)->create([
            'sale_id' => $this->sale->id,
            'payment_date' => now()->toDateString(),
            'amount' => 50000,
        ], $this->admin->id);

        $this->actingAs($this->admin);
    }

    public function test_authenticated_admin_pages_render_successfully(): void
    {
        $urls = [
            route('dashboard.index'),
            route('sales.index'),
            route('sales.create'),
            route('sales.show', $this->sale),
            route('sales.edit', $this->sale),
            route('payments.index'),
            route('payments.create'),
            route('payments.show', $this->payment),
            route('payments.edit', $this->payment),
            route('users.index'),
            route('users.create'),
            route('users.edit', $this->editableUser),
            route('roles.index'),
            route('roles.create'),
            route('roles.edit', $this->editableRole),
            route('permissions.index'),
            route('items.index'),
            route('items.create'),
            route('items.edit', $this->item),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_datatable_endpoints_return_json_successfully(): void
    {
        $this->getJson(route('sales.data'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('payments.data'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('users.data'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('roles.data'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('permissions.data'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('items.data'))->assertOk()->assertJsonStructure(['data']);
    }

    public function test_permission_module_is_read_only(): void
    {
        $this->assertFalse(Route::has('permissions.create'));
        $this->assertFalse(Route::has('permissions.store'));
        $this->assertFalse(Route::has('permissions.edit'));
        $this->assertFalse(Route::has('permissions.update'));
        $this->assertFalse(Route::has('permissions.destroy'));
    }
}
