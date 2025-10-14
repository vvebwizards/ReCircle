<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtAuthenticate::class,
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'check.blocked.user' => \App\Http\Middleware\CheckBlockedUser::class,
        ]);

        // Add global middleware
        $middleware->web(append: [
            \App\Http\Middleware\CheckBlockedUser::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\CheckBlockedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
