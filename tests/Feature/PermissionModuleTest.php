<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac\Permissions;
use App\Support\Rbac\Roles;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionModuleTest extends TestCase
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

    public function test_permission_datatable_exposes_seeded_permission_labels(): void
    {
        $this->getJson(route('permissions.data'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => Permissions::DASHBOARD_VIEW,
                'module' => 'Dashboard',
                'label' => 'Lihat dashboard',
            ]);
    }
}
