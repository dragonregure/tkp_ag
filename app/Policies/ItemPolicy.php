<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use App\Support\Rbac\Permissions;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::ITEMS_VIEW);
    }

    public function view(User $user, Item $item): bool
    {
        return $user->hasPermissionTo(Permissions::ITEMS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::ITEMS_CREATE);
    }

    public function update(User $user, Item $item): bool
    {
        return $user->hasPermissionTo(Permissions::ITEMS_UPDATE);
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->hasPermissionTo(Permissions::ITEMS_DELETE);
    }
}
