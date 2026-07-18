<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemModuleTest extends TestCase
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

    public function test_admin_can_create_update_and_delete_item(): void
    {
        $token = 'test-token';

        $this->withSession(['_token' => $token])->post(route('items.store'), [
            '_token' => $token,
            'code' => 'ITM-TEST-001',
            'name' => 'Item Test',
            'price' => 125000,
            'stock' => 10,
        ])
            ->assertRedirect(route('items.index'));

        $item = Item::query()->where('code', 'ITM-TEST-001')->firstOrFail();

        $this->put(route('items.update', $item), [
            '_token' => $token,
            'code' => 'ITM-TEST-002',
            'name' => 'Item Test Updated',
            'price' => 150000,
            'stock' => 15,
        ])
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'code' => 'ITM-TEST-002',
            'name' => 'Item Test Updated',
            'stock' => 15,
        ]);

        $this->delete(route('items.destroy', $item->refresh()), ['_token' => $token])
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
