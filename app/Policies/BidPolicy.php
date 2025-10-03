<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\User;
use App\Models\WasteItem;

class BidPolicy
{
    public function viewAny(User $user, WasteItem $wasteItem): bool
    {
        // Allow generator or any authenticated user to view bids (could restrict later)
        return $user->id === $wasteItem->generator_id || true;
    }

    public function view(User $user, Bid $bid): bool
    {
        return $user->id === $bid->maker_id || $user->id === $bid->wasteItem->generator_id;
    }

    public function create(User $user, WasteItem $wasteItem): bool
    {
        // cannot bid on own waste item
        return $user->id !== $wasteItem->generator_id;
    }

    public function update(User $user, Bid $bid): bool
    {
        return $user->id === $bid->maker_id;
    }

    public function updateStatus(User $user, Bid $bid): bool
    {
        // only generator can accept/reject
        return $user->id === $bid->wasteItem->generator_id;
    }

    public function withdraw(User $user, Bid $bid): bool
    {
        return $user->id === $bid->maker_id;
    }
}
