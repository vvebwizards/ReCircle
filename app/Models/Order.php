<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id', 'product_id', 'quantity', 'unit_price', 'total_amount', 'status', 'shipping_address', 'payment_gateway', 'payment_intent_id', 'placed_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'placed_at' => 'datetime',
        'status' => OrderStatus::class,
        'payment_gateway' => PaymentGateway::class,
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
