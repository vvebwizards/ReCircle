<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['jwt.auth'])->get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])->name('dashboard.bids');

Route::get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])
    ->middleware(['jwt.auth'])
    ->name('dashboard.bids');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

Route::get('/maker/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])
    ->middleware(['jwt.auth'])
    ->name('maker.analytics');

Route::middleware(['jwt.auth'])->get('/maker/bids', [\App\Http\Controllers\MakerBidController::class, 'index'])->name('maker.bids');

Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth'])->name('admin.users');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggleStatus');

    Route::post('/users/{user}/block', [UserManagementController::class, 'blockUser'])->name('admin.users.block');
    Route::post('/users/{user}/unblock', [UserManagementController::class, 'unblockUser'])->name('admin.users.unblock');

    require __DIR__.'/admin_listings.php';
});

Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');

require __DIR__.'/auth.php';

require __DIR__.'/materials.php';

require __DIR__.'/products.php';

Route::middleware(['jwt.auth'])->group(function () {
    require __DIR__.'/waste_items.php';
});

require __DIR__.'/marketplace.php';

require __DIR__.'/bids.php';
