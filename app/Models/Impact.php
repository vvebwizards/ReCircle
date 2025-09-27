<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Impact extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_type', 'subject_id', 'co2_kg_saved', 'landfill_kg_avoided', 'distance_km', 'computed_at', 'calc_details',
    ];

    protected $casts = [
        'co2_kg_saved' => 'decimal:3',
        'landfill_kg_avoided' => 'decimal:3',
        'distance_km' => 'decimal:2',
        'computed_at' => 'datetime',
        'calc_details' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
