<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/auth', [AuthController::class, 'show'])->name('auth');

Route::get('/twofa', function () {
    return view('auth.twofa');
})->name('twofa');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password');

// Registration (uses the tabbed /auth page for UI)
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
