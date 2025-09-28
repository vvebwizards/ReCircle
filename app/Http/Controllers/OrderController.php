<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $quantity = $request->input('quantity');
        $unit_price = $request->input('unit_price');
        $total_amount = $quantity * $unit_price;

        $shipping_address = [
            'street' => 'Rue de Jardin',
            'city' => 'Chartage',
            'postal_code' => '1143',
            'country' => 'Tunisia',
        ];

        $order = Order::create([
            'buyer_id' => 2, // hardcoded for now
            'product_id' => 4, // or null, since no real product ID
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

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:255',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('cart.index')
                ->withErrors($validator)
                ->withInput();
        }

        $order->update([
            'quantity' => $request->input('quantity'),
            'total_amount' => $request->input('quantity') * $order->unit_price,
            'shipping_address' => $request->input('shipping_address'),
        ]);

        return redirect()->route('cart.index')->with('success', 'Order updated successfully.');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('cart.index')->with('success', 'Order removed from cart.');
    }
}
