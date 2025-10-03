<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

// Admin routes (role protection removed per request)
Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    Route::get('/dashboard', function () {
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

require __DIR__.'/auth.php';

require __DIR__.'/materials.php';

require __DIR__.'/products.php';

Route::middleware(['jwt.auth'])->group(function () {
    require __DIR__.'/waste_items.php';
});
