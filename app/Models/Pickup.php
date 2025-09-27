<?php

namespace App\Models;

use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'courier_id', 'scheduled_pickup_window_start', 'scheduled_pickup_window_end', 'status', 'tracking_code', 'picked_up_at', 'notes',
    ];

    protected $casts = [
        'scheduled_pickup_window_start' => 'datetime',
        'scheduled_pickup_window_end' => 'datetime',
        'picked_up_at' => 'datetime',
        'status' => PickupStatus::class,
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
