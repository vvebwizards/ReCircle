<?php

// routes/badges.php

use App\Http\Controllers\BadgeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    // Badges & Achievements
    Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::get('/leaderboard', [BadgeController::class, 'leaderboard'])->name('badges.leaderboard');
    Route::get('/profile/{user}/badges', [BadgeController::class, 'showUserBadges'])->name('badges.user-profile');
});
