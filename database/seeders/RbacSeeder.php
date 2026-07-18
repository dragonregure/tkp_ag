<?php

namespace Database\Seeders;

use App\Support\Rbac\Permissions;
use App\Support\Rbac\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::query()->firstOrCreate([
            'name' => Roles::ADMIN,
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions(Permissions::all());

        $manager = Role::query()->firstOrCreate([
            'name' => Roles::MANAGER,
            'guard_name' => 'web',
        ]);
        $manager->syncPermissions([
            Permissions::DASHBOARD_VIEW,
            Permissions::SALES_VIEW,
            Permissions::SALES_CREATE,
            Permissions::SALES_UPDATE,
            Permissions::PAYMENTS_VIEW,
            Permissions::PAYMENTS_CREATE,
            Permissions::PAYMENTS_UPDATE,
            Permissions::USERS_VIEW,
            Permissions::ITEMS_VIEW,
            Permissions::ROLES_VIEW,
            Permissions::PERMISSIONS_VIEW,
        ]);

        $sales = Role::query()->firstOrCreate([
            'name' => Roles::SALES,
            'guard_name' => 'web',
        ]);
        $sales->syncPermissions([
            Permissions::DASHBOARD_VIEW,
            Permissions::SALES_VIEW,
            Permissions::SALES_CREATE,
            Permissions::SALES_UPDATE,
            Permissions::PAYMENTS_VIEW,
            Permissions::ITEMS_VIEW,
        ]);

        $cashier = Role::query()->firstOrCreate([
            'name' => Roles::CASHIER,
            'guard_name' => 'web',
        ]);
        $cashier->syncPermissions([
            Permissions::DASHBOARD_VIEW,
            Permissions::SALES_VIEW,
            Permissions::PAYMENTS_VIEW,
            Permissions::PAYMENTS_CREATE,
            Permissions::PAYMENTS_UPDATE,
            Permissions::ITEMS_VIEW,
        ]);

        $inventory = Role::query()->firstOrCreate([
            'name' => Roles::INVENTORY,
            'guard_name' => 'web',
        ]);
        $inventory->syncPermissions([
            Permissions::DASHBOARD_VIEW,
            Permissions::SALES_VIEW,
            Permissions::PAYMENTS_VIEW,
            Permissions::ITEMS_VIEW,
            Permissions::ITEMS_CREATE,
            Permissions::ITEMS_UPDATE,
            Permissions::ITEMS_DELETE,
        ]);

        $staff = Role::query()->firstOrCreate([
            'name' => Roles::STAFF,
            'guard_name' => 'web',
        ]);
        $staff->syncPermissions([
            Permissions::DASHBOARD_VIEW,
            Permissions::SALES_VIEW,
            Permissions::SALES_CREATE,
            Permissions::PAYMENTS_VIEW,
            Permissions::PAYMENTS_CREATE,
            Permissions::ITEMS_VIEW,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
