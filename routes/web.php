<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Dashboard bids (generator) - only accessible when authenticated via JWT
Route::middleware(['jwt.auth'])->get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])->name('dashboard.bids');

Route::get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])
    ->middleware(['jwt.auth'])
    ->name('dashboard.bids');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

// Maker bids list (bids the current maker has placed)
Route::middleware(['jwt.auth'])->get('/maker/bids', [\App\Http\Controllers\MakerBidController::class, 'index'])->name('maker.bids');

// Admin routes (role protection removed per request)
Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth'])->name('admin.users');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
    // Admin listings routes (limited CRUD: no create/store)
    require __DIR__.'/admin_listings.php';
});
// Security settings (2FA) page — gated client-side via /api/auth/me
Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');

// Auth routes
require __DIR__.'/auth.php';

// Material routes
require __DIR__.'/materials.php';
// Waste item routes (generator-facing)
// Generator waste item routes (role protection removed per request)
Route::middleware(['jwt.auth'])->group(function () {
    require __DIR__.'/waste_items.php';
});

// Marketplace routes (authenticated browse)
require __DIR__.'/marketplace.php';

// Bid routes
require __DIR__.'/bids.php';
