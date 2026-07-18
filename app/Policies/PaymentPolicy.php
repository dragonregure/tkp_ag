<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Support\Rbac\Permissions;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::PAYMENTS_VIEW);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(Permissions::PAYMENTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permissions::PAYMENTS_CREATE);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(Permissions::PAYMENTS_UPDATE);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo(Permissions::PAYMENTS_DELETE);
    }
}
