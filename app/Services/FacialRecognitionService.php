<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFaceDescriptor;
use App\Notifications\FailedFacialVerificationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class FacialRecognitionService
{
    /**
     * Verify a user's identity using facial recognition
     *
     * @param  string  $email  User's email
     * @param  array  $faceDescriptor  Face descriptor array from frontend
     * @return array Result containing success status and user data if successful
     */
    public function verifyFacialIdentity(string $email, array $faceDescriptor): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        if (! $user) {
            Log::warning('Facial verification attempted for non-existent user', ['email' => $email]);

            return [
                'success' => false,
                'message' => 'User not found',
                'user' => null,
            ];
        }

        // Check if user has facial recognition registered
        if (! $user->is_facial_registered) {
            Log::info('Facial verification attempted for user without facial registration', ['user_id' => $user->id]);

            return [
                'success' => false,
                'message' => 'Facial recognition not registered for this user',
                'user' => null,
            ];
        }

        // Get user's stored face descriptor
        $storedDescriptor = UserFaceDescriptor::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $storedDescriptor) {
            Log::warning('No active face descriptor found for user', ['user_id' => $user->id]);

            return [
                'success' => false,
                'message' => 'No facial data found for verification',
                'user' => null,
            ];
        }

        // Calculate similarity between provided descriptor and stored descriptor
        $threshold = config('auth.facial_recognition_threshold', 0.6);
        $distance = $this->calculateEuclideanDistance($faceDescriptor, $storedDescriptor->descriptor);

        Log::info('Facial recognition verification attempt', [
            'user_id' => $user->id,
            'email' => $email,
            'distance' => $distance,
            'threshold' => $threshold,
            'match' => $distance < $threshold,
        ]);

        if ($distance < $threshold) {
            // Update last used timestamp
            $storedDescriptor->update(['last_used' => now()]);

            return [
                'success' => true,
                'message' => 'Facial verification successful',
                'user' => $user,
                'confidence' => 1 - ($distance / $threshold), // Convert distance to confidence score
            ];
        }

        return [
            'success' => false,
            'message' => 'Facial verification failed - face does not match',
            'user' => null,
            'confidence' => 1 - ($distance / $threshold),
        ];
    }

    /**
     * Calculate Euclidean distance between two face descriptors
     */
    private function calculateEuclideanDistance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            throw new \InvalidArgumentException('Face descriptors must have the same length');
        }

        $sum = 0;
        for ($i = 0; $i < count($descriptor1); $i++) {
            $sum += pow($descriptor1[$i] - $descriptor2[$i], 2);
        }

        return sqrt($sum);
    }

    /**
     * Check if user can use facial fallback
     */
    public function canUseFacialFallback(User $user): bool
    {
        return $user->is_facial_registered &&
               $user->shouldTriggerFacialFallback() &&
               ! $user->isBlocked() &&
               ! $user->isLockedOut();
    }

    /**
     * Process successful facial fallback verification
     * Resets failed login attempts and logs the event
     */
    public function processFacialFallbackSuccess(User $user): void
    {
        $user->resetFailedLoginAttempts();

        Log::info('Facial fallback verification successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'previous_failed_attempts' => $user->failed_login_attempts,
        ]);
    }

    /**
     * Process failed facial fallback verification
     * May lock the account and notify admins
     */
    public function processFacialFallbackFailure(User $user): array
    {
        $lockoutDuration = config('auth.lockout_duration_minutes', 30);
        $user->lockForDuration($lockoutDuration);

        Log::warning('Facial fallback verification failed - account locked', [
            'user_id' => $user->id,
            'email' => $user->email,
            'failed_attempts' => $user->failed_login_attempts,
            'locked_until' => $user->locked_until,
        ]);

        // Notify admins about failed facial verification
        $this->notifyAdminsOfFailedVerification($user);

        return [
            'account_locked' => true,
            'locked_until' => $user->locked_until,
            'message' => "Account temporarily locked due to failed verification. Try again after {$lockoutDuration} minutes.",
        ];
    }

    /**
     * Notify administrators about failed facial verification attempt
     */
    private function notifyAdminsOfFailedVerification(User $user): void
    {
        try {
            // Get request details
            $request = request();
            $ipAddress = $request->ip() ?? 'Unknown';
            $userAgent = $request->userAgent() ?? 'Unknown';

            // Get all admin users
            $admins = User::where('role', \App\Enums\UserRole::ADMIN)->get();

            if ($admins->isNotEmpty()) {
                // Send synchronously so DB notifications are persisted immediately
                Notification::sendNow(
                    $admins,
                    new FailedFacialVerificationNotification(
                        $user,
                        $user->failed_login_attempts,
                        $ipAddress,
                        $userAgent
                    )
                );

                Log::info('Admin notification sent for failed facial verification', [
                    'user_id' => $user->id,
                    'admin_count' => $admins->count(),
                    'ip_address' => $ipAddress,
                ]);
            } else {
                Log::warning('No admin users found to notify about failed facial verification', [
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification for failed facial verification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
