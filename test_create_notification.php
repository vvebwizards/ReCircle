<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Notifications\FailedFacialVerificationNotification;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Find or create a test user
    $user = User::where('email', 'test@example.com')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com', 
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
            'onboarding_completed' => true
        ]);
    }
    
    // Get all admin users
    $admins = User::where('role', 'admin')->get();
    
    if ($admins->count() === 0) {
        echo "No admin users found. Creating one...\n";
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'onboarding_completed' => true
        ]);
        $admins = collect([$admin]);
    }
    
    echo "Found " . $admins->count() . " admin users\n";
    
    // Create test notification data
    $notificationData = [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'failed_attempts' => 3,
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'timestamp' => now()->toISOString(),
        'severity' => 'high'
    ];
    
    // Send notification to all admins
    foreach ($admins as $admin) {
        $admin->notify(new FailedFacialVerificationNotification($notificationData));
        echo "Notification sent to admin: {$admin->name} ({$admin->email})\n";
    }
    
    echo "\nTest notification created successfully!\n";
    echo "Visit: http://127.0.0.1:8000/admin/notifications to view\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}