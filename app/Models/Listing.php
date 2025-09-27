<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'waste_item_id', 'status', 'min_price', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'min_price' => 'decimal:2',
        'status' => ListingStatus::class,
    ];

    public function wasteItem(): BelongsTo
    {
        return $this->belongsTo(WasteItem::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'listing_id');
    }
}
