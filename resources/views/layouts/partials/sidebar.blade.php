<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard.index') }}" class="brand-link">
            <span class="brand-image rounded-circle d-inline-flex align-items-center justify-content-center opacity-75 shadow">
                <i class="bi bi-receipt-cutoff text-white"></i>
            </span>
            <span class="brand-text fw-light">TKP AG</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2 h-100" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column h-100" data-lte-toggle="treeview" data-accordion="false">
                @can(\App\Support\Rbac\Permissions::DASHBOARD_VIEW)
                    <x-admin.nav-item route="dashboard.index" icon="bi bi-speedometer2" label="Dashboard" />
                @endcan
                @can(\App\Support\Rbac\Permissions::SALES_VIEW)
                    <x-admin.nav-item route="sales.index" icon="bi bi-cart-check" label="Penjualan" />
                @endcan
                @can(\App\Support\Rbac\Permissions::PAYMENTS_VIEW)
                    <x-admin.nav-item route="payments.index" icon="bi bi-cash-coin" label="Pembayaran" />
                @endcan

                @if (auth()->user()?->can(\App\Support\Rbac\Permissions::USERS_VIEW)
                    || auth()->user()?->can(\App\Support\Rbac\Permissions::ROLES_VIEW)
                    || auth()->user()?->can(\App\Support\Rbac\Permissions::PERMISSIONS_VIEW)
                    || auth()->user()?->can(\App\Support\Rbac\Permissions::ITEMS_VIEW))
                    <li class="nav-item has-treeview @if (request()->routeIs('users.*', 'roles.*', 'permissions.*', 'items.*')) menu-open @endif">
                        <a href="#" @class(['nav-link', 'active' => request()->routeIs('users.*', 'roles.*', 'permissions.*', 'items.*')])>
                            <i class="nav-icon bi bi-database"></i>
                            <p>Master <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can(\App\Support\Rbac\Permissions::USERS_VIEW)
                                <x-admin.nav-item route="users.index" icon="bi bi-people" label="User" />
                            @endcan
                            @can(\App\Support\Rbac\Permissions::ROLES_VIEW)
                                <x-admin.nav-item route="roles.index" icon="bi bi-person-badge" label="Role" />
                            @endcan
                            @can(\App\Support\Rbac\Permissions::PERMISSIONS_VIEW)
                                <x-admin.nav-item route="permissions.index" icon="bi bi-shield-lock" label="Permission" />
                            @endcan
                            @can(\App\Support\Rbac\Permissions::ITEMS_VIEW)
                                <x-admin.nav-item route="items.index" icon="bi bi-box-seam" label="Item" />
                            @endcan
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
