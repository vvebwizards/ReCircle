<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    public const CATEGORIES = ['wood', 'metal', 'plastic', 'textile', 'electronic', 'glass', 'paper'];
    public const UNITS = ['kg', 'pcs', 'm2', 'l'];

    protected $fillable = [
        'name',
        'category',
        'unit',
        'quantity',
        'recyclability_score',
        'maker_id',
        'description',
        'waste_item_id',
        'co2_kg_saved',
        'landfill_kg_avoided',
        'energy_saved_kwh',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'recyclability_score' => 'integer',
        'co2_kg_saved' => 'decimal:2',
        'landfill_kg_avoided' => 'decimal:2',
        'energy_saved_kwh' => 'decimal:2',
    ];


    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function wasteItem(): BelongsTo
    {
        return $this->belongsTo(WasteItem::class, 'waste_item_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MaterialImage::class);
    }

    public function processSteps(): HasMany
    {
        return $this->hasMany(ProcessStep::class, 'material_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_materials')
            ->withPivot('quantity_used', 'unit')
            ->withTimestamps();
    }

    public function getPrimaryImageUrlAttribute()
    {
        return $this->images->first()->image_url ?? asset('images/default-material.png');
    }
}
