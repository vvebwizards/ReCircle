<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
class Delivery extends Model
{
   // use SoftDeletes;
    protected $dates = ['deleted_at'];

    public const STATUS_SCHEDULED  = 'scheduled';
    public const STATUS_ASSIGNED   = 'assigned';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'pickup_id',
        'courier_id',
        'courier_phone',
        'hub_address',
        'hub_lat',
        'hub_lng',
        'status',
        'tracking_code',
        'assigned_at',
        'picked_up_at',
        'arrived_hub_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at'   => 'datetime',
        'picked_up_at'  => 'datetime',
        'arrived_hub_at'=> 'datetime',
        'hub_lat'       => 'float',
        'hub_lng'       => 'float',
    ];

    /** Relations */
    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }
    
public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string)$term);
        if ($term === '') return $q;

        return $q->where(function($b) use ($term) {
            $b->where('tracking_code','like',"%{$term}%")
              ->orWhereHas('pickup', fn($p) =>
                    $p->where('pickup_address','like',"%{$term}%")
              )
              ->orWhereHas('pickup.wasteItem', fn($w) =>
                    $w->where('title','like',"%{$term}%")
              );
        });
    }

public function scopeCompleted($q) {
    return $q->whereIn('status', ['delivered','failed','cancelled']);
}

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /** Helpers de statut */
    public function markAssigned(?int $courierId = null, ?string $phone = null): void
    {
        $this->fill([
            'status'      => self::STATUS_ASSIGNED,
            'courier_id'  => $courierId ?? $this->courier_id,
            'courier_phone' => $phone ?? $this->courier_phone,
            'assigned_at' => now(),
        ])->save();
    }

    public function markInTransit(): void
    {
        $this->update([
            'status'       => self::STATUS_IN_TRANSIT,
            'picked_up_at' => $this->picked_up_at ?? now(),
        ]);
    }

    public function markDelivered(): void
    {
        $this->update([
            'status'        => self::STATUS_DELIVERED,
            'arrived_hub_at'=> now(),
        ]);
    }
    
    // Filtrer par courier connecté
    public function scopeForCourier(Builder $q, int $courierId): Builder
    {
        return $q->where(function ($w) use ($courierId) {
            $w->whereNull('courier_id')->orWhere('courier_id', $courierId);
        });
    }

    // Montrer les courses “actives”
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['scheduled','assigned','in_transit']);
    }
}
