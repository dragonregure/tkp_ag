<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use App\Support\Rbac\Permissions;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::SALES_VIEW);
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo(Permissions::SALES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::SALES_CREATE);
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo(Permissions::SALES_UPDATE);
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo(Permissions::SALES_DELETE);
    }
}
