<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\ExpenseController as CashierExpenseController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\ShiftController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\TrackController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\ExpenseCategoryController;
use App\Http\Controllers\Owner\ExpenseController as OwnerExpenseController;
use App\Http\Controllers\Owner\MenuItemController;
use App\Http\Controllers\Owner\StaffController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\QueueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Public display screen (TV / monitor warung)
Route::get('/display', [DisplayController::class, 'index'])->name('display');

// Public customer routes (no auth required)
Route::get('/order/{qrCode}', [CustomerOrderController::class, 'menu'])->name('order.menu');
Route::get('/order/{qrCode}/checkout', [CustomerOrderController::class, 'checkout'])->name('order.checkout');
Route::post('/order/{qrCode}', [CustomerOrderController::class, 'store'])->name('order.store');
Route::get('/order/track/{order}', [TrackController::class, 'show'])->name('order.track')->middleware('signed');

// Generic dashboard (customer fallback)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// Owner routes
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('staff', StaffController::class);
    Route::patch('staff/{staff}/toggle-active', [StaffController::class, 'toggleActive'])->name('staff.toggle-active');

    // Expense management
    Route::get('/expenses', [OwnerExpenseController::class, 'index'])->name('expenses');
    Route::patch('/expenses/{expense}/approve', [OwnerExpenseController::class, 'approve'])->name('expenses.approve');
    Route::patch('/expenses/{expense}/reject', [OwnerExpenseController::class, 'reject'])->name('expenses.reject');

    // Expense categories
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    Route::patch('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
});

// Menu & Table management — accessible by owner and cashier
Route::middleware(['auth', 'role:owner|cashier'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('menu-items', MenuItemController::class)->except(['show']);
    Route::patch('menu-items/{menuItem}/toggle-active', [MenuItemController::class, 'toggleActive'])->name('menu-items.toggle-active');

    // print-all must be registered before the {table} resource routes
    Route::get('tables/print-all', [TableController::class, 'printAll'])->name('tables.print-all');
    Route::resource('tables', TableController::class)->except(['show']);
    Route::get('tables/{table}/qr', [TableController::class, 'qr'])->name('tables.qr');
    Route::patch('tables/{table}/toggle-active', [TableController::class, 'toggleActive'])->name('tables.toggle-active');
});

// Cashier routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');

    // Shift
    Route::post('/shift/start', [ShiftController::class, 'start'])->name('shift.start');
    Route::post('/shift/end', [ShiftController::class, 'end'])->name('shift.end');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/order', [PosController::class, 'store'])->name('pos.store');

    // Order management
    Route::get('/orders', [CashierOrderController::class, 'index'])->name('orders');
    Route::patch('/orders/{order}/confirm', [CashierOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/complete', [CashierOrderController::class, 'complete'])->name('orders.complete');

    // Expense input
    Route::get('/expenses', [CashierExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses', [CashierExpenseController::class, 'store'])->name('expenses.store');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

    // Kitchen queue
    Route::get('/queue', [QueueController::class, 'index'])->name('queue');
    Route::patch('/queue/{order}/update-status', [QueueController::class, 'updateStatus'])->name('queue.update');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
