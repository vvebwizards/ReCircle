<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id', 'maker_id', 'sku', 'name', 'description', 'images', 'material_passport', 'stock', 'price', 'status', 'published_at',
    ];

    protected $casts = [
        'images' => 'array',
        'material_passport' => 'array',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
        'status' => ProductStatus::class,
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function impacts(): MorphMany
    {
        return $this->morphMany(Impact::class, 'subject');
    }
}
