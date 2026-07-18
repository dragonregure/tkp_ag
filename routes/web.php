<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Support\Rbac\Permissions;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:' . Permissions::DASHBOARD_VIEW)
        ->name('dashboard.index');

    Route::prefix('sales')->name('sales.')->middleware('permission:' . Permissions::SALES_VIEW)->group(function (): void {
        Route::get('/data', [SaleController::class, 'data'])->name('data');
        Route::get('/select2', [SaleController::class, 'select2'])->name('select2');
    });
    Route::resource('sales', SaleController::class)
        ->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'permission:' . Permissions::SALES_VIEW)
        ->middlewareFor(['create', 'store'], 'permission:' . Permissions::SALES_CREATE)
        ->middlewareFor(['edit', 'update'], 'permission:' . Permissions::SALES_UPDATE);
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])
        ->middleware('permission:' . Permissions::SALES_DELETE)
        ->name('sales.destroy');

    Route::prefix('payments')->name('payments.')->middleware('permission:' . Permissions::PAYMENTS_VIEW)->group(function (): void {
        Route::get('/data', [PaymentController::class, 'data'])->name('data');
    });
    Route::resource('payments', PaymentController::class)
        ->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'permission:' . Permissions::PAYMENTS_VIEW)
        ->middlewareFor(['create', 'store'], 'permission:' . Permissions::PAYMENTS_CREATE)
        ->middlewareFor(['edit', 'update'], 'permission:' . Permissions::PAYMENTS_UPDATE);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
        ->middleware('permission:' . Permissions::PAYMENTS_DELETE)
        ->name('payments.destroy');

    Route::prefix('master')->group(function (): void {
        Route::prefix('users')->name('users.')->middleware('permission:' . Permissions::USERS_VIEW)->group(function (): void {
            Route::get('/data', [UserController::class, 'data'])->name('data');
        });
        Route::resource('users', UserController::class)
            ->except(['show', 'destroy'])
            ->middlewareFor('index', 'permission:' . Permissions::USERS_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . Permissions::USERS_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . Permissions::USERS_UPDATE);
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:' . Permissions::USERS_DELETE)
            ->name('users.destroy');

        Route::prefix('roles')->name('roles.')->middleware('permission:' . Permissions::ROLES_VIEW)->group(function (): void {
            Route::get('/data', [RoleController::class, 'data'])->name('data');
        });
        Route::resource('roles', RoleController::class)
            ->except(['show', 'destroy'])
            ->middlewareFor('index', 'permission:' . Permissions::ROLES_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . Permissions::ROLES_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . Permissions::ROLES_UPDATE);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:' . Permissions::ROLES_DELETE)
            ->name('roles.destroy');

        Route::prefix('permissions')->name('permissions.')->middleware('permission:' . Permissions::PERMISSIONS_VIEW)->group(function (): void {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/data', [PermissionController::class, 'data'])->name('data');
        });

        Route::prefix('items')->name('items.')->middleware('permission:' . Permissions::ITEMS_VIEW)->group(function (): void {
            Route::get('/data', [ItemController::class, 'data'])->name('data');
            Route::get('/select2', [ItemController::class, 'select2'])->name('select2');
        });
        Route::resource('items', ItemController::class)
            ->except(['show', 'destroy'])
            ->middlewareFor('index', 'permission:' . Permissions::ITEMS_VIEW)
            ->middlewareFor(['create', 'store'], 'permission:' . Permissions::ITEMS_CREATE)
            ->middlewareFor(['edit', 'update'], 'permission:' . Permissions::ITEMS_UPDATE);
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])
            ->middleware('permission:' . Permissions::ITEMS_DELETE)
            ->name('items.destroy');
    });
});
