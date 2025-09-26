<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'listing_id', 'maker_id', 'bid_price', 'proposed_timeline_days', 'message', 'status', 'accepted_at',
    ];

    protected $casts = [
        'bid_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'status' => MatchStatus::class,
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function pickup(): HasOne
    {
        return $this->hasOne(Pickup::class, 'match_id');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class, 'match_id');
    }
}
