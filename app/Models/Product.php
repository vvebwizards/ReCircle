<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'maker_id',
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


    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_id');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
            ->withPivot('quantity_used', 'unit')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id');
    }


    public function generateMaterialPassport()
    {
        $materialsData = $this->materials->map(fn ($material) => [
            'name' => $material->name,
            'category' => $material->category,
            'quantity_used' => $material->pivot->quantity_used,
            'unit' => $material->pivot->unit,
            'recyclability_score' => $material->recyclability_score,
            'co2_kg_saved' => $material->co2_kg_saved,
            'landfill_kg_avoided' => $material->landfill_kg_avoided,
            'energy_saved_kwh' => $material->energy_saved_kwh,
        ]);

        $passport = [
            'materials' => $materialsData,
            'maker' => $this->maker ? [
                'name' => $this->maker->name,
                'company' => $this->maker->company_name,
            ] : null,
            'production_date' => $this->created_at->toISOString(),
            'care_instructions' => $this->care_instructions,
            'warranty' => $this->warranty_months ? "{$this->warranty_months} months" : null,
        ];

        $this->update(['material_passport' => $passport]);
        return $passport;
    }
}
