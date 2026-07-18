<?php

namespace App\Support\Rbac;

final class Roles
{
    public const ADMIN = 'admin';

    public const MANAGER = 'manager';

    public const SALES = 'sales';

    public const CASHIER = 'cashier';

    public const INVENTORY = 'inventory';

    public const STAFF = 'staff';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::MANAGER,
            self::SALES,
            self::CASHIER,
            self::INVENTORY,
            self::STAFF,
        ];
    }
}
