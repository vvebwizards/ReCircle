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

Route::prefix('admin')->middleware(['jwt.auth','role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth','role:admin'])->name('admin.users');
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
Route::middleware(['jwt.auth','role:generator'])->group(function(){
    require __DIR__.'/waste_items.php';
});
