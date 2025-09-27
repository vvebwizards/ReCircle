<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id', 'material_id', 'step_name', 'description', 'sort_order', 'inputs', 'estimated_hours', 'actual_hours', 'media', 'qc_pass', 'notes', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'inputs' => 'array',
        'media' => 'array',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'qc_pass' => 'bool',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
