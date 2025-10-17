<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only register broadcast routes if Pusher class exists
        // This prevents errors in CI environments where the package might not be installed
        if (class_exists('Pusher\Pusher')) {
            Broadcast::routes();
            require base_path('routes/channels.php');
        }
    }
}
