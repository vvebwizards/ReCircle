<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Channel for all bids on a specific waste item
Broadcast::channel('waste-item.{wasteItemId}.bids', function ($user, $wasteItemId) {
    return true; // Public channel, anyone can listen
});

// Channel for user's bids activity
Broadcast::channel('user.{userId}.bids', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
