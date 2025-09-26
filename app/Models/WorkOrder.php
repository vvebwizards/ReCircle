<?php

namespace App\Models;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'type', 'title', 'status', 'started_at', 'completed_at', 'cost_breakdown',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cost_breakdown' => 'array',
        'status' => WorkOrderStatus::class,
        'type' => WorkOrderType::class,
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchModel::class, 'match_id');
    }

    public function processSteps(): HasMany
    {
        return $this->hasMany(ProcessStep::class, 'work_order_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'work_order_id');
    }
}
