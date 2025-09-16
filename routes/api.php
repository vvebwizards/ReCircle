<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Middleware\JwtAuthenticate;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('auth/login', [ApiAuthController::class, 'login']);
    Route::post('auth/refresh', [ApiAuthController::class, 'refresh']);
    Route::post('auth/logout', [ApiAuthController::class, 'logout']);

    Route::middleware(JwtAuthenticate::class)->group(function () {
        Route::get('auth/me', [ApiAuthController::class, 'me']);
    });
});
