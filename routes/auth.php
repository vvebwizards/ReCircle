<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Middleware\JwtAuthenticate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/auth', [AuthController::class, 'show'])->name('auth');

Route::get('/twofa', function () {
    return view('auth.twofa');
})->name('twofa');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password');

// Registration (uses the tabbed /auth page for UI)
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Google OAuth
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// Choose role after social signup (requires JWT cookie)
Route::middleware(JwtAuthenticate::class)->group(function () {
    Route::get('/auth/choose-role', [RoleController::class, 'show'])->name('choose-role.show');
    Route::post('/auth/choose-role', [RoleController::class, 'store'])->name('choose-role.store');
});

// Email verification
Route::get('/email/verify', function () {
    return redirect()->to(route('auth').'#signin')->with('verify_message', 'We sent you a verification email.');
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

    return redirect()->to(route('auth').'#signin')->with('verify_message', 'Email verified. You can sign in now.');
})->name('verification.verify');

// Resend verification email
Route::post('/email/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');

// API authentication endpoints (JWT) now colocated here
Route::prefix('api/auth')->group(function () {
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::post('refresh', [ApiAuthController::class, 'refresh']);
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::middleware(JwtAuthenticate::class)->get('me', [ApiAuthController::class, 'me']);

    // 2FA management (requires authenticated user)
    Route::middleware(JwtAuthenticate::class)->group(function () {
        Route::get('2fa/status', [TwoFactorController::class, 'status']);
        Route::get('2fa/setup', [TwoFactorController::class, 'setup']);
        Route::post('2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('2fa/disable', [TwoFactorController::class, 'disable']);
    });

    // Email code send (does not require JWT yet; validates credentials)
    Route::post('2fa/email/send', [ApiAuthController::class, 'sendEmailCode']);
});

// Facial Recognition Authentication Routes
use App\Http\Controllers\FaceAuthController;
use App\Http\Controllers\OnboardingController;

Route::get('/facial-login', [FaceAuthController::class, 'showFacialLogin'])->name('facial.login');
Route::post('/api/face/enroll', [FaceAuthController::class, 'enroll'])->middleware('jwt.auth');
Route::post('/api/face/authenticate', [FaceAuthController::class, 'authenticate']);

// Onboarding Routes (require authentication)
Route::middleware('jwt.auth')->prefix('onboarding')->group(function () {
    Route::post('/setup-2fa', [OnboardingController::class, 'setup2FA']);
    Route::post('/verify-2fa', [OnboardingController::class, 'verify2FA']);
    Route::post('/avatar', [OnboardingController::class, 'updateAvatar']);
    Route::post('/complete', [OnboardingController::class, 'completeOnboarding']);
});
