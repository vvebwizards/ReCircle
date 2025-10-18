<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class AdminCartController extends Controller
{
    // List carts for admin
    public function index(Request $request)
    {
        $query = Cart::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $carts = $query->paginate(20)->withQueryString();

        return view('admin.carts.index', compact('carts'));
    }

    // Show a single cart and its items
    public function show(Cart $cart)
    {
        $cart->load('user', 'items.product', 'items.bid');

        return view('admin.carts.show', compact('cart'));
    }

    // Update cart (for admin quick edit: status or total)
    public function update(Request $request, Cart $cart)
    {
        $data = $request->validate([
            'status' => 'nullable|string',
            'total_amount' => 'nullable|numeric',
        ]);

        $cart->update(array_filter($data, fn ($v) => ! is_null($v)));

        return back()->with('success', 'Cart updated.');
    }

    // Delete a cart
    public function destroy(Cart $cart)
    {
        $cart->items()->delete();
        $cart->delete();

        return redirect()->route('admin.carts.index')->with('success', 'Cart deleted.');
    }
}
