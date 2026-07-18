<?php

namespace Tests\Unit;

use App\Support\Rbac\Permissions;
use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
    public function test_all_returns_every_defined_permission_once(): void
    {
        $permissions = Permissions::all();

        $this->assertCount(count(array_unique($permissions)), $permissions);
        $this->assertContains(Permissions::DASHBOARD_VIEW, $permissions);
        $this->assertContains(Permissions::SALES_CREATE, $permissions);
        $this->assertContains(Permissions::PAYMENTS_DELETE, $permissions);
        $this->assertContains(Permissions::PERMISSIONS_VIEW, $permissions);
    }

    public function test_labels_and_groups_are_resolved_for_known_permissions(): void
    {
        $this->assertSame('Lihat dashboard', Permissions::label(Permissions::DASHBOARD_VIEW));
        $this->assertSame('Tambah penjualan', Permissions::label(Permissions::SALES_CREATE));
        $this->assertSame('Sales', Permissions::group(Permissions::SALES_CREATE));
        $this->assertSame('custom.permission', Permissions::label('custom.permission'));
    }

    public function test_grouped_returns_permissions_by_module(): void
    {
        $grouped = Permissions::grouped();

        $this->assertArrayHasKey('Dashboard', $grouped);
        $this->assertArrayHasKey('Sales', $grouped);
        $this->assertSame('Lihat dashboard', $grouped['Dashboard'][Permissions::DASHBOARD_VIEW]);
        $this->assertSame('Hapus penjualan', $grouped['Sales'][Permissions::SALES_DELETE]);
    }
}
