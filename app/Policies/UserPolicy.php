<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Rbac\Permissions;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::USERS_VIEW);
    }

    public function view(User $user, User $managedUser): bool
    {
        return $user->hasPermissionTo(Permissions::USERS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::USERS_CREATE);
    }

    public function update(User $user, User $managedUser): bool
    {
        return $user->hasPermissionTo(Permissions::USERS_UPDATE);
    }

    public function delete(User $user, User $managedUser): bool
    {
        return $user->hasPermissionTo(Permissions::USERS_DELETE);
    }
}
