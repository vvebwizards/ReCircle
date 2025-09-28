<?php

namespace App\Http\Controllers;

use App\Models\Order;

class CartController extends Controller
{
    public function index()
    {
        // Retrieve all orders for the logged-in user (or buyer_id = 2 for now)
        $orders = Order::with('product')->where('buyer_id', 2)->get();

        return view('cart.cart', compact('orders'));
    }
}
