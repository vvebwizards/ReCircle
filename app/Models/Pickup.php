<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
     protected $table = 'pickups';
   // public $timestamps = false; // si tu gères created_at/updated_at à la main

    protected $fillable = [
       'waste_item_id',
        'courier_id',
        'pickup_address',
        'scheduled_pickup_window_start',
        'scheduled_pickup_window_end',
        'status',
        'tracking_code',
        'notes',
    ];

    protected $casts = [
        'scheduled_pickup_window_start' => 'datetime',
        'scheduled_pickup_window_end'   => 'datetime',
       // 'match_id'   => 'integer',   // OK: laisser tel quel
       // 'courier_id' => 'integer',
       // 'picked_up_at'                  => 'datetime',
    ];

    public function wasteItem() { return $this->belongsTo(\App\Models\WasteItem::class); }
    public function courier()   { return $this->belongsTo(\App\Models\User::class, 'courier_id'); }
    public function delivery(){    return $this->hasOne(\App\Models\Delivery::class);}

}