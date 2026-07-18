<?php

namespace App\Providers;

use App\Contracts\CodeGeneratorInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Contracts\ItemRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\SaleRepositoryInterface;
use App\Contracts\RoleRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\PermissionRepositoryInterface;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Policies\ItemPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SalePolicy;
use App\Policies\UserPolicy;
use App\Repositories\PermissionRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\ItemRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SaleRepository;
use App\Repositories\UserRepository;
use App\Services\CodeGenerator;
use App\Support\Rbac\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CodeGeneratorInterface::class, CodeGenerator::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(SpatiePermission::class, PermissionPolicy::class);

        foreach (Permissions::all() as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->hasPermissionTo($permission));
        }
    }
}
