<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Generic dashboard (customer fallback)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// Role-based dashboards
Route::middleware(['auth'])->group(function () {
    Route::get('/owner/dashboard', function () {
        return Inertia::render('Owner/Dashboard');
    })->middleware('role:owner')->name('owner.dashboard');

    Route::get('/cashier/dashboard', function () {
        return Inertia::render('Cashier/Dashboard');
    })->middleware('role:cashier')->name('cashier.dashboard');

    Route::get('/staff/dashboard', function () {
        return Inertia::render('Staff/Dashboard');
    })->middleware('role:staff')->name('staff.dashboard');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
