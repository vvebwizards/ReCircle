<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generator_id',
        'material_id',
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

    /**
     * @return BelongsTo<User, static>
     */
    public function generator(): BelongsTo
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsTo(User::class, 'generator_id');
    }

    /**
     * @return BelongsTo<Material, static>
     */
    public function material(): BelongsTo
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsTo(Material::class);
    }
}
