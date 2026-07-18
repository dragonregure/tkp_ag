<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Rbac\Permissions;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::PERMISSIONS_VIEW);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo(Permissions::PERMISSIONS_VIEW);
    }
}
