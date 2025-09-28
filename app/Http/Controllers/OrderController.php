<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // You can adjust these values as needed
        $quantity = 3;
        $unit_price = 5.00;
        $total_amount = $quantity * $unit_price;
        $shipping_address = [
            'street' => '123 Main St',
            'city' => 'SomeCity',
            'postal_code' => '12345',
            'country' => 'CountryName',
        ];

        $order = Order::create([
            'buyer_id' => 2, // hardcoded
            'product_id' => 4, // hardcoded
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_amount' => $total_amount,
            'status' => OrderStatus::PENDING,
            'shipping_address' => $shipping_address,
            'payment_gateway' => PaymentGateway::STRIPE,
            'payment_intent_id' => null,
            'placed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ]);
    }
}
