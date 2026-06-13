<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.menu.index');
    }

    return redirect()->route('login');
});

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

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

    Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
        Route::get('/', [OrderAdminController::class, 'index'])->name('index');
        Route::get('/data', [OrderAdminController::class, 'data'])->name('data');
        Route::get('/{order}/ticket', [OrderAdminController::class, 'ticket'])->name('ticket');
        Route::post('/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('status');
    });

    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit.index');
});

Route::get('/m/{token}', [PublicMenuController::class, 'show'])->name('public.menu.show');
Route::post('/m/{token}/order', [PublicMenuController::class, 'storeOrder'])->name('public.menu.order');