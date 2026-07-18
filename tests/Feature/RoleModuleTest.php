<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\Permissions;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleModuleTest extends TestCase
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

    public function test_admin_can_create_update_and_delete_role(): void
    {
        $token = 'test-token';

        $this->withSession(['_token' => $token])->post(route('roles.store'), [
            '_token' => $token,
            'name' => 'auditor',
            'permissions' => [Permissions::DASHBOARD_VIEW],
        ])
            ->assertRedirect(route('roles.index'));

        $role = Role::findByName('auditor');
        $this->assertTrue($role->hasPermissionTo(Permissions::DASHBOARD_VIEW));

        $this->put(route('roles.update', $role), [
            '_token' => $token,
            'name' => 'senior-auditor',
            'permissions' => [Permissions::DASHBOARD_VIEW, Permissions::SALES_VIEW],
        ])
            ->assertRedirect(route('roles.index'));

        $role = Role::findByName('senior-auditor');
        $this->assertTrue($role->hasPermissionTo(Permissions::SALES_VIEW));

        $this->delete(route('roles.destroy', $role), ['_token' => $token])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'senior-auditor']);
    }

    public function test_admin_role_cannot_be_deleted(): void
    {
        $token = 'test-token';
        $adminRole = Role::findByName(Roles::ADMIN);

        $this->withSession(['_token' => $token])
            ->from(route('roles.index'))
            ->delete(route('roles.destroy', $adminRole), ['_token' => $token])
            ->assertRedirect(route('roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => Roles::ADMIN]);
    }
}
