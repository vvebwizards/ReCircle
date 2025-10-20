<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFaceDescriptor extends Model
{
    protected $fillable = ['user_id', 'descriptor', 'is_active'];

    protected $casts = [
        'descriptor' => 'array',
        'last_used' => 'datetime',
    ];

    protected static function booted()
    {
        // Automatically set is_facial_registered when face descriptor is created/updated
        static::created(function ($faceDescriptor) {
            if ($faceDescriptor->is_active) {
                $faceDescriptor->user()->update(['is_facial_registered' => true]);
            }
        });

        static::updated(function ($faceDescriptor) {
            // Update the flag based on whether user has any active descriptors
            $hasActiveFace = static::where('user_id', $faceDescriptor->user_id)
                ->where('is_active', true)
                ->exists();

            $faceDescriptor->user()->update(['is_facial_registered' => $hasActiveFace]);
        });

        static::deleted(function ($faceDescriptor) {
            // Check if user still has other active descriptors
            $hasActiveFace = static::where('user_id', $faceDescriptor->user_id)
                ->where('is_active', true)
                ->exists();

            $faceDescriptor->user()->update(['is_facial_registered' => $hasActiveFace]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findSimilarFace($inputDescriptor, $threshold = 0.6)
    {
        $descriptors = self::where('is_active', true)->get();

        foreach ($descriptors as $stored) {
            $distance = self::calculateEuclideanDistance($inputDescriptor, $stored->descriptor);
            if ($distance < $threshold) {
                return $stored->user;
            }
        }

        return null;
    }

    private static function calculateEuclideanDistance($desc1, $desc2)
    {
        $sum = 0;
        for ($i = 0; $i < count($desc1); $i++) {
            $sum += pow($desc1[$i] - $desc2[$i], 2);
        }

        return sqrt($sum);
    }
}
