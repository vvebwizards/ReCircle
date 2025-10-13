<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FacialRecognitionService;
use Illuminate\Console\Command;

class TestAdminNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test admin notification system for failed facial verification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List current users
        $users = User::where('role', '!=', 'admin')->get(['id', 'name', 'email', 'is_facial_registered', 'failed_login_attempts']);

        $this->info("Current users:");
        foreach($users as $user) {
            $facial = $user->is_facial_registered ? 'Yes' : 'No';
            $this->line("ID: {$user->id} | {$user->name} | {$user->email} | Facial: {$facial} | Fails: {$user->failed_login_attempts}");
        }

        // Test admin notification by simulating a failed facial verification
        $testUser = $users->where('is_facial_registered', true)->first();

        if ($testUser) {
            $this->info("\nTesting admin notification with user: {$testUser->name} ({$testUser->email})");
            
            // Simulate failed login attempts to trigger facial fallback
            $testUser->update(['failed_login_attempts' => 3]);
            
            // Create facial recognition service
            $facialService = app(FacialRecognitionService::class);
            
            // Simulate failed facial verification
            $result = $facialService->processFacialFallbackFailure($testUser);
            
            $this->info("✅ Admin notification test completed.");
            $this->info("Result: " . json_encode($result, JSON_PRETTY_PRINT));
            
            // Check admin notifications
            $adminCount = User::where('role', 'admin')->count();
            $this->info("Admin users who should receive notification: {$adminCount}");
            
        } else {
            $this->warn("No users with facial registration found to test with.");
        }

        return 0;
    }
}
