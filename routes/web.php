<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('jwt.auth')->name('dashboard');

// API endpoint for getting current user data
Route::get('/api/user', function () {
    try {
        $user = auth()->user();
        if ($user) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ]);
        }

        return response()->json(['error' => 'Not authenticated'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error: '.$e->getMessage()], 500);
    }
})->middleware('jwt.auth');

// Dashboard bids (generator) - only accessible when authenticated via JWT
Route::middleware(['jwt.auth'])->get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])->name('dashboard.bids');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

// Maker bids list (bids the current maker has placed)
Route::middleware(['jwt.auth'])->get('/maker/bids', [\App\Http\Controllers\MakerBidController::class, 'index'])->name('maker.bids');

// Admin routes (role protection removed per request)
Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth'])->name('admin.users');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggleStatus');

    // New blocking routes
    Route::post('/users/{user}/block', [UserManagementController::class, 'blockUser'])->name('admin.users.block');
    Route::post('/users/{user}/unblock', [UserManagementController::class, 'unblockUser'])->name('admin.users.unblock');
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    // Admin listings routes (limited CRUD: no create/store)
    require __DIR__.'/admin_listings.php';
    
    // Admin notifications routes (consolidated, ordered to avoid /{id} swallowing others)
    Route::prefix('notifications')->group(function () {
        // List
        Route::get('/', [\App\Http\Controllers\AdminNotificationController::class, 'index'])->name('admin.notifications.index');
        // JSON count endpoints (declare BEFORE /{id})
        Route::get('/unread-count', [\App\Http\Controllers\AdminNotificationController::class, 'unreadCount'])->name('admin.notifications.unread-count');
        // Legacy alias kept for compatibility with older JS
        Route::get('/api/unread-count', [\App\Http\Controllers\AdminNotificationController::class, 'unreadCount'])->name('admin.notifications.unread-count-legacy');
        // Actions
        Route::patch('/{id}/read', [\App\Http\Controllers\AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read');
        Route::patch('/mark-all-read', [\App\Http\Controllers\AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
        // Show and delete (after specific routes)
        Route::get('/{id}', [\App\Http\Controllers\AdminNotificationController::class, 'show'])->name('admin.notifications.show');
        Route::delete('/{id}', [\App\Http\Controllers\AdminNotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    });
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

// Marketplace routes (authenticated browse)
require __DIR__.'/marketplace.php';

// Bid routes
require __DIR__.'/bids.php';

// routes/web.php
require __DIR__.'/forum.php';

require __DIR__.'/badges.php';
