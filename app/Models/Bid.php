<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $table = 'waste_bids';

    protected $fillable = [
        'waste_item_id',
        'maker_id',
        'amount',
        'currency',
        'notes',
        'status',
        'accepted_at',
        'rejected_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const CURRENCIES = ['USD', 'EUR', 'TND'];

    public function wasteItem(): BelongsTo
    {
        return $this->belongsTo(WasteItem::class);
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function markAccepted(): void
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->accepted_at = now();
        $this->save();
    }

    public function markRejected(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_at = now();
        $this->save();
    }

    public function markWithdrawn(): void
    {
        $this->status = self::STATUS_WITHDRAWN;
        $this->withdrawn_at = now();
        $this->save();
    }
}
