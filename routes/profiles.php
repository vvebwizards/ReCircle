<?php
// routes/profiles.php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    // User Profiles
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('/profile/{user}/followers', [ProfileController::class, 'followers'])->name('profiles.followers');
    Route::get('/profile/{user}/following', [ProfileController::class, 'following'])->name('profiles.following');
    Route::get('/profile/{user}/activity', [ProfileController::class, 'activity'])->name('profiles.activity');    
    // Follow actions
    Route::post('/profile/{user}/follow', [ProfileController::class, 'follow'])->name('profiles.follow');
    Route::post('/profile/{user}/unfollow', [ProfileController::class, 'unfollow'])->name('profiles.unfollow');
});