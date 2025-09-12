<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Models\User;

Route::get('/auth', [AuthController::class, 'show'])->name('auth');

Route::get('/twofa', function () {
    return view('auth.twofa');
})->name('twofa');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password');

// Registration (uses the tabbed /auth page for UI)
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Email verification
Route::get('/email/verify', function () {
    return redirect()->to(route('auth') . '#signin')->with('verify_message', 'We sent you a verification email.');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    if (! $request->hasValidSignature()) {
        abort(403);
    }
    $user = User::findOrFail($id);
    if (! hash_equals($hash, sha1($user->email))) {
        abort(403);
    }
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
    return redirect()->to(route('auth') . '#signin')->with('verify_message', 'Email verified. You can sign in now.');
})->name('verification.verify');

// Resend verification email
Route::post('/email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');
