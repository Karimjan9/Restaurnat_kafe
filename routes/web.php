<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StationTicketController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});



Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change');
    Route::put('/password/change', [PasswordChangeController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/cabinet', CabinetController::class)->name('cabinet');

    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::view('/waiter', 'waiter.index')
        ->middleware('permission:waiter.panel')
        ->name('waiter.index');

    Route::view('/kitchen', 'stations.kitchen')
        ->middleware('permission:kitchen.view')
        ->name('kitchen.index');

    Route::view('/bar', 'stations.bar')
        ->middleware('permission:bar.view')
        ->name('bar.index');

    Route::get('/stations/{station}/orders/{order}/ticket', [StationTicketController::class, 'show'])
        ->middleware('auth')
        ->name('stations.ticket');

    Route::view('/pos', 'pos.index')
        ->middleware('permission:orders.create')
        ->name('pos.index');

    Route::get('/operations', [OperationsController::class, 'index'])
        ->middleware('permission:operations.manage')
        ->name('operations.index');
    Route::patch('/operations/tables/{table}/status', [OperationsController::class, 'updateTableStatus'])
        ->middleware('permission:operations.manage')
        ->name('operations.tables.status');
    Route::patch('/operations/orders/{order}/move-table', [OperationsController::class, 'moveTable'])
        ->middleware('permission:operations.manage')
        ->name('operations.orders.move-table');
    Route::post('/operations/orders/merge', [OperationsController::class, 'mergeOrders'])
        ->middleware('permission:operations.manage')
        ->name('operations.orders.merge');
    Route::patch('/operations/orders/{order}/discount', [OperationsController::class, 'applyDiscount'])
        ->middleware('permission:operations.manage')
        ->name('operations.orders.discount');
    Route::patch('/operations/orders/{order}/void', [OperationsController::class, 'voidOrder'])
        ->middleware('permission:refunds.manage')
        ->name('operations.orders.void');
    Route::post('/operations/orders/{order}/refund', [OperationsController::class, 'refundOrder'])
        ->middleware('permission:refunds.manage')
        ->name('operations.orders.refund');
    Route::post('/operations/shifts', [OperationsController::class, 'openShift'])
        ->middleware('permission:shifts.manage')
        ->name('operations.shifts.open');
    Route::patch('/operations/shifts/{shift}/close', [OperationsController::class, 'closeShift'])
        ->middleware('permission:shifts.manage')
        ->name('operations.shifts.close');

    Route::get('/staff', [StaffController::class, 'index'])
        ->middleware('permission:staff.manage')
        ->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])
        ->middleware('permission:staff.manage')
        ->name('staff.store');
    Route::put('/staff/{user}', [StaffController::class, 'update'])
        ->middleware('permission:staff.manage')
        ->name('staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])
        ->middleware('permission:staff.manage')
        ->name('staff.destroy');

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.manage')
        ->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.manage')
        ->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.manage')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.manage')
        ->name('roles.destroy');

    Route::get('/branches', [BranchController::class, 'index'])
        ->middleware('permission:branches.manage')
        ->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])
        ->middleware('permission:branches.manage')
        ->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])
        ->middleware('permission:branches.manage')
        ->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])
        ->middleware('permission:branches.manage')
        ->name('branches.destroy');

    Route::get('/tables', [DiningTableController::class, 'index'])
        ->middleware('permission:tables.manage')
        ->name('tables.index');
    Route::post('/tables', [DiningTableController::class, 'store'])
        ->middleware('permission:tables.manage')
        ->name('tables.store');
    Route::put('/tables/{table}', [DiningTableController::class, 'update'])
        ->middleware('permission:tables.manage')
        ->name('tables.update');
    Route::delete('/tables/{table}', [DiningTableController::class, 'destroy'])
        ->middleware('permission:tables.manage')
        ->name('tables.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:categories.manage')
        ->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:categories.manage')
        ->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:categories.manage')
        ->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:categories.manage')
        ->name('categories.destroy');

    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:products.manage')
        ->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:products.manage')
        ->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.manage')
        ->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('permission:products.manage')
        ->name('products.destroy');

    Route::get('/modifiers', [ModifierController::class, 'index'])
        ->middleware('permission:products.manage')
        ->name('modifiers.index');
    Route::post('/modifiers/groups', [ModifierController::class, 'storeGroup'])
        ->middleware('permission:products.manage')
        ->name('modifiers.groups.store');
    Route::put('/modifiers/groups/{group}', [ModifierController::class, 'updateGroup'])
        ->middleware('permission:products.manage')
        ->name('modifiers.groups.update');
    Route::delete('/modifiers/groups/{group}', [ModifierController::class, 'destroyGroup'])
        ->middleware('permission:products.manage')
        ->name('modifiers.groups.destroy');
    Route::post('/modifiers/groups/{group}/options', [ModifierController::class, 'storeOption'])
        ->middleware('permission:products.manage')
        ->name('modifiers.groups.options.store');
    Route::put('/modifiers/options/{option}', [ModifierController::class, 'updateOption'])
        ->middleware('permission:products.manage')
        ->name('modifiers.options.update');
    Route::delete('/modifiers/options/{option}', [ModifierController::class, 'destroyOption'])
        ->middleware('permission:products.manage')
        ->name('modifiers.options.destroy');

    Route::get('/orders/{order}/receipt', [ReceiptController::class, 'show'])
        ->middleware('permission:orders.view')
        ->name('orders.receipt');
    Route::get('/orders/{order}/check', [ReceiptController::class, 'check'])
        ->middleware('permission:orders.view')
        ->name('orders.check');

    Route::get('/reports', ReportController::class)
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])
        ->middleware('permission:reports.view')
        ->name('reports.export');
});
