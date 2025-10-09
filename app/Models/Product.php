<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'maker_id',
        'material_id', // Direct link to source material
        'sku',
        'name',
        'description',
        'material_passport',
        'stock',
        'price',
        'status',
        'published_at',
        'dimensions',
        'weight',
        'care_instructions',
        'warranty_months',
        'is_featured',
        'tags',
    ];

    protected $casts = [
        'material_passport' => 'array',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
        'status' => ProductStatus::class,
        'dimensions' => 'array',
        'weight' => 'decimal:2',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id');
    }

    public function impacts(): MorphMany
    {
        return $this->morphMany(Impact::class, 'subject');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
            ->withPivot('quantity_used', 'unit')
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductStatus::PUBLISHED);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('material', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images->first()->image_path ?? null;
    }

    public function getPrimaryImageUrlAttribute()
    {
        return $this->images->first()->image_url ?? asset('images/default-product.png');
    }

    public function getAllImageUrlsAttribute()
    {
        return $this->images->pluck('image_url')->toArray();
    }

    public function getFormattedPriceAttribute()
    {
        return '€'.number_format($this->price, 2);
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock === 0) {
            return 'out_of_stock';
        } elseif ($this->stock < 10) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function publish()
    {
        $this->update([
            'status' => ProductStatus::PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function unpublish()
    {
        $this->update(['status' => ProductStatus::DRAFT]);
    }

    public function updateStock($quantity)
    {
        $this->update(['stock' => max(0, $quantity)]);

        if ($this->stock === 0) {
            $this->update(['status' => ProductStatus::SOLD_OUT]);
        }
    }

    public function generateMaterialPassport()
    {
        $passport = [
            'source_material' => $this->material ? [
                'name' => $this->material->name,
                'category' => $this->material->category,
                'recyclability_score' => $this->material->recyclability_score,
            ] : null,
            'production_date' => $this->created_at->toISOString(),
            'maker' => $this->maker ? [
                'name' => $this->maker->name,
                'company' => $this->maker->company_name,
            ] : null,
            'environmental_impact' => $this->impacts->first() ? [
                'co2_saved' => $this->impacts->first()->co2_kg_saved,
                'landfill_avoided' => $this->impacts->first()->landfill_kg_avoided,
            ] : null,
            'care_instructions' => $this->care_instructions,
            'warranty' => $this->warranty_months ? $this->warranty_months.' months' : null,
        ];

        $this->update(['material_passport' => $passport]);

        return $passport;
    }
}
