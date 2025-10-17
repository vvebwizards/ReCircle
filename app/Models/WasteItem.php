<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'title',
        'images',
        'estimated_weight',
        'condition',
        'location',
        'notes',
    ];

    protected $casts = [
        'images' => 'array',
        'location' => 'array',
        'estimated_weight' => 'decimal:2',
    ];

    public function generator(): BelongsTo
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsTo(User::class, 'generator_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'waste_item_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WasteItemImage::class)->orderBy('order');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'waste_item_id');
    }

    public function getPrimaryImageAttribute(): ?string
    {
        // relation now 'photos' to avoid collision with images attribute cast
        return $this->photos->first()->image_path ?? null;
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        return $this->photos->first()->image_url ?? asset('images/default-material.png');
    }

    public function pickups()
    {
        return $this->hasMany(\App\Models\Pickup::class);
    }
}
