<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncFacialRegistrationFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facial:sync-flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync is_facial_registered flags for users with existing face descriptors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing facial registration flags...');

        // Get all users with active face descriptors
        $usersWithFaces = \App\Models\UserFaceDescriptor::where('is_active', true)
            ->with('user')
            ->get()
            ->pluck('user_id')
            ->unique();

        // Set is_facial_registered = true for users with active face descriptors
        $updatedCount = \App\Models\User::whereIn('id', $usersWithFaces)
            ->where('is_facial_registered', false)
            ->update(['is_facial_registered' => true]);

        // Set is_facial_registered = false for users without active face descriptors
        $clearedCount = \App\Models\User::whereNotIn('id', $usersWithFaces)
            ->where('is_facial_registered', true)
            ->update(['is_facial_registered' => false]);

        $this->info("Updated {$updatedCount} users to have facial registration enabled");
        $this->info("Cleared {$clearedCount} users without active face descriptors");

        // Show current stats
        $totalWithFacial = \App\Models\User::where('is_facial_registered', true)->count();
        $this->info("Total users with facial registration: {$totalWithFacial}");

        return 0;
    }
}
