<?php

namespace App\Support\Rbac;

final class Permissions
{
    public const DASHBOARD_VIEW = 'dashboard.view';

    public const SALES_VIEW = 'sales.view';

    public const SALES_CREATE = 'sales.create';

    public const SALES_UPDATE = 'sales.update';

    public const SALES_DELETE = 'sales.delete';

    public const PAYMENTS_VIEW = 'payments.view';

    public const PAYMENTS_CREATE = 'payments.create';

    public const PAYMENTS_UPDATE = 'payments.update';

    public const PAYMENTS_DELETE = 'payments.delete';

    public const USERS_VIEW = 'users.view';

    public const USERS_CREATE = 'users.create';

    public const USERS_UPDATE = 'users.update';

    public const USERS_DELETE = 'users.delete';

    public const ITEMS_VIEW = 'items.view';

    public const ITEMS_CREATE = 'items.create';

    public const ITEMS_UPDATE = 'items.update';

    public const ITEMS_DELETE = 'items.delete';

    public const ROLES_VIEW = 'roles.view';

    public const ROLES_CREATE = 'roles.create';

    public const ROLES_UPDATE = 'roles.update';

    public const ROLES_DELETE = 'roles.delete';

    public const PERMISSIONS_VIEW = 'permissions.view';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DASHBOARD_VIEW,
            self::SALES_VIEW,
            self::SALES_CREATE,
            self::SALES_UPDATE,
            self::SALES_DELETE,
            self::PAYMENTS_VIEW,
            self::PAYMENTS_CREATE,
            self::PAYMENTS_UPDATE,
            self::PAYMENTS_DELETE,
            self::USERS_VIEW,
            self::USERS_CREATE,
            self::USERS_UPDATE,
            self::USERS_DELETE,
            self::ITEMS_VIEW,
            self::ITEMS_CREATE,
            self::ITEMS_UPDATE,
            self::ITEMS_DELETE,
            self::ROLES_VIEW,
            self::ROLES_CREATE,
            self::ROLES_UPDATE,
            self::ROLES_DELETE,
            self::PERMISSIONS_VIEW,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DASHBOARD_VIEW => 'Lihat dashboard',
            self::SALES_VIEW => 'Lihat penjualan',
            self::SALES_CREATE => 'Tambah penjualan',
            self::SALES_UPDATE => 'Ubah penjualan',
            self::SALES_DELETE => 'Hapus penjualan',
            self::PAYMENTS_VIEW => 'Lihat pembayaran',
            self::PAYMENTS_CREATE => 'Tambah pembayaran',
            self::PAYMENTS_UPDATE => 'Ubah pembayaran',
            self::PAYMENTS_DELETE => 'Hapus pembayaran',
            self::USERS_VIEW => 'Lihat user',
            self::USERS_CREATE => 'Tambah user',
            self::USERS_UPDATE => 'Ubah user',
            self::USERS_DELETE => 'Hapus user',
            self::ITEMS_VIEW => 'Lihat item',
            self::ITEMS_CREATE => 'Tambah item',
            self::ITEMS_UPDATE => 'Ubah item',
            self::ITEMS_DELETE => 'Hapus item',
            self::ROLES_VIEW => 'Lihat role',
            self::ROLES_CREATE => 'Tambah role',
            self::ROLES_UPDATE => 'Ubah role',
            self::ROLES_DELETE => 'Hapus role',
            self::PERMISSIONS_VIEW => 'Lihat permission',
        ];
    }

    public static function label(string $permission): string
    {
        return self::labels()[$permission] ?? $permission;
    }

    public static function group(string $permission): string
    {
        return ucfirst((string) str($permission)->before('.')->replace('-', ' '));
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::all() as $permission) {
            $grouped[self::group($permission)][$permission] = self::label($permission);
        }

        return $grouped;
    }
}
