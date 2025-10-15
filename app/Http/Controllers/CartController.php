<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CartController extends Controller
{
    // View the cart
    public function viewCart()
    {
        $user = Auth::user();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending']
        );

        $cartItems = $cart->items()
            ->when($user->role === UserRole::BUYER, fn ($q) => $q->with('product'))
            ->when($user->role === UserRole::MAKER, fn ($q) => $q->with('bid'))
            ->get();

        $orders = $cartItems;

        return view('cart.index', compact('orders', 'cart'));
    }

    // Add item to cart (Product for Buyer, Bid for Maker)
    public function addToCart(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'pending']);

        if ($user->role === UserRole::BUYER) {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::findOrFail($request->product_id);

            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'status' => 'pending',
                'type' => 'product',
            ]);
        } elseif ($user->role === UserRole::MAKER) {
            $request->validate([
                'bid_id' => 'required|exists:waste_bids,id',
            ]);

            $bid = Bid::findOrFail($request->bid_id);

            if ($bid->status !== Bid::STATUS_ACCEPTED) {
                return back()->with('error', 'Only accepted bids can be added to the cart.');
            }

            $cart->items()->create([
                'bid_id' => $bid->id,
                'quantity' => 1,
                'price' => $bid->amount,
                'status' => 'pending',
                'type' => 'bid',
            ]);
        }

        return back()->with('success', 'Item added to cart.');
    }

    // Checkout using Stripe
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['items' => function ($q) {
                $q->where('status', 'pending')->with('product', 'bid');
            }])
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = $cart->items->map(function ($item) use ($user) {
            // Buyer: use product data
            if ($user->role === UserRole::BUYER && $item->product) {
                $amountCents = (int) round(((float) $item->price) * 100);

                return [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product->name,
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => (int) $item->quantity,
                ];
            }

            // Maker: use bid data (fallback to item price)
            if ($user->role === UserRole::MAKER && $item->bid) {
                $amountCents = (int) round(((float) $item->price ?: $item->bid->amount) * 100);

                return [
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => 'Bid #'.$item->bid->id,
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => (int) $item->quantity,
                ];
            }

            return null;
        })->filter()->values()->toArray();

        // Ensure there is at least one line item for Stripe Checkout
        if (empty($lineItems)) {
            return back()->with('error', 'No valid items found for checkout.');
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            // Include the Checkout Session ID in the success URL so we can retrieve it
            'success_url' => route('cart.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cart.cancel'),
        ]);

        return redirect($session->url);
    }

    // Handle success callback from Stripe
    public function success(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->where('status', 'pending')->first();

        if (! $cart) {
            return view('cart.success');
        }

        // If Stripe session_id is provided, retrieve the session to get payment details
        $sessionId = $request->get('session_id');

        if ($sessionId) {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            try {
                // Expand payment_intent to get payment intent id and amounts
                $session = StripeSession::retrieve($sessionId, ['expand' => ['payment_intent']]);

                $amountTotal = $session->amount_total ?? ($session->payment_intent->amount ?? null);
                $paymentIntentId = is_object($session->payment_intent) ? $session->payment_intent->id : $session->payment_intent;

                $updateData = ['status' => 'paid'];

                if (! is_null($amountTotal)) {
                    // Store as decimal in the cart (assumes DB expects decimal in main currency units)
                    $updateData['total_amount'] = ($amountTotal / 100);
                }

                if (! empty($paymentIntentId)) {
                    $updateData['stripe_payment_intent_id'] = $paymentIntentId;
                }

                DB::transaction(function () use ($cart, $updateData, $user) {
                    // Update cart status and totals
                    $cart->update($updateData);

                    // For buyer purchases, decrement product stock for product-type items
                    if ($user->role === UserRole::BUYER) {
                        $cart->items()->where('status', 'pending')->with('product')->get()->each(function ($item) {
                            if ($item->type === 'product' && $item->product) {
                                $product = $item->product;
                                $decrement = (int) $item->quantity;
                                // Ensure stock does not go negative
                                $newStock = max(0, $product->stock - $decrement);
                                $product->update(['stock' => $newStock]);
                            }
                        });
                    }

                    // Mark cart items as paid instead of deleting them
                    $cart->items()->where('status', 'pending')->update(['status' => 'paid']);
                });
            } catch (\Exception $e) {
                // If retrieving the session fails, we still mark paid but leave amounts null
                // Optionally log the exception here
                $cart->update(['status' => 'paid']);
                $cart->items()->where('status', 'pending')->update(['status' => 'paid']);
            }
        } else {
            // No session id provided: fallback to marking the cart paid (existing behavior)
            $cart->update(['status' => 'paid']);
        }

        // Do not delete items after checkout — keep them for record and mark them as paid above.

        return view('cart.success');
    }

    // Handle cancellation
    public function cancel()
    {
        return view('cart.cancel');
    }
}
