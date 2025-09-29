<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
     protected $table = 'pickups';
    public $timestamps = false; // si tu gères created_at/updated_at à la main

    protected $fillable = [
       // 'match_id',
        'courier_id',
        'scheduled_pickup_window_start',
        'scheduled_pickup_window_end',
       // 'picked_up_at',
        'status',
        'tracking_code',
        'pickup_address',
        'notes',
    ];

    protected $casts = [
        'scheduled_pickup_window_start' => 'datetime',
        'scheduled_pickup_window_end'   => 'datetime',
        'match_id'   => 'integer',   // OK: laisser tel quel
        'courier_id' => 'integer',
       // 'picked_up_at'                  => 'datetime',
    ];

    public function match()   { return $this->belongsTo(\App\Models\Match::class); }
    public function courier() { return $this->belongsTo(\App\Models\User::class, 'courier_id'); }
}
