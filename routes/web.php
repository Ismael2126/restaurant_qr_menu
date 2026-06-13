<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->route(match ($user->role) {
            'admin' => 'admin.menu.index',
            default => 'admin.orders.index',
        });
    }

    return redirect()->route('login');
});

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');

    Route::middleware('password.change')->group(function () {

        Route::middleware('role:admin')->group(function () {
            Route::prefix('admin/menu')->name('admin.menu.')->group(function () {
                Route::get('/', [MenuAdminController::class, 'index'])->name('index');

                Route::post('/categories', [MenuAdminController::class, 'storeCategory'])->name('categories.store');
                Route::post('/categories/{category}/toggle', [MenuAdminController::class, 'toggleCategory'])->name('categories.toggle');
                Route::delete('/categories/{category}', [MenuAdminController::class, 'deleteCategory'])->name('categories.delete');

                Route::post('/items', [MenuAdminController::class, 'storeItem'])->name('items.store');
                Route::get('/items/{menuItem}/edit', [MenuAdminController::class, 'editItem'])->name('items.edit');
                Route::put('/items/{menuItem}', [MenuAdminController::class, 'updateItem'])->name('items.update');
                Route::post('/items/{menuItem}/toggle', [MenuAdminController::class, 'toggleItem'])->name('items.toggle');
                Route::delete('/items/{menuItem}', [MenuAdminController::class, 'deleteItem'])->name('items.delete');

                Route::post('/tables', [MenuAdminController::class, 'storeTable'])->name('tables.store');
                Route::post('/tables/{restaurantTable}/toggle', [MenuAdminController::class, 'toggleTable'])->name('tables.toggle');
                Route::get('/tables/{restaurantTable}/qr', [MenuAdminController::class, 'qrPage'])->name('tables.qr');
            });

            Route::prefix('admin/users')->name('admin.users.')->group(function () {
                Route::get('/', [UserAdminController::class, 'index'])->name('index');
                Route::post('/', [UserAdminController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [UserAdminController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserAdminController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserAdminController::class, 'destroy'])->name('destroy');
            });

            Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit.index');

            Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
                Route::get('/', [SettingsController::class, 'edit'])->name('edit');
                Route::put('/', [SettingsController::class, 'update'])->name('update');
            });
        });

        Route::middleware('role:admin,kitchen,cashier')->group(function () {
            Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
                Route::get('/', [OrderAdminController::class, 'index'])->name('index');
                Route::get('/data', [OrderAdminController::class, 'data'])->name('data');
                Route::get('/{order}/ticket', [OrderAdminController::class, 'ticket'])->name('ticket');
                Route::post('/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('status');
            });
        });

        Route::middleware('role:admin,cashier')->group(function () {
            Route::prefix('admin/purchases')->name('admin.purchases.')->group(function () {
                Route::get('/', [PurchaseController::class, 'index'])->name('index');
                Route::post('/', [PurchaseController::class, 'store'])->name('store');
                Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
                Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');
                Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('/output-tax/export', [ReportController::class, 'exportOutputTax'])->name('output-tax.export');
                Route::get('/input-tax/export', [ReportController::class, 'exportInputTax'])->name('input-tax.export');
            });
        });
    });
});

Route::get('/m/{token}', [PublicMenuController::class, 'show'])->name('public.menu.show');
Route::post('/m/{token}/order', [PublicMenuController::class, 'storeOrder'])->name('public.menu.order');
