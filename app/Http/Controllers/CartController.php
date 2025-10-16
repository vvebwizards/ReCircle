<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Bid;
use App\Models\Cart;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CartController extends Controller
{
    public function index(Request $request)
{
    $query = Cart::with('user');

    // --- Search ---
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('id', $search)
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    // --- Status filter ---
    if ($status = $request->input('status')) {
        $query->where('status', $status);
    }

    // --- Min/Max total filter ---
    if ($min = $request->input('min_total')) {
        $query->where('total_amount', '>=', $min);
    }
    if ($max = $request->input('max_total')) {
        $query->where('total_amount', '<=', $max);
    }

    // --- Sorting ---
    if ($sort = $request->input('sort')) {
        switch ($sort) {
            case 'total_asc':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'total_desc':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
                $query->orderBy('created_at', 'desc');
                break;
        }
    } else {
        $query->latest();
    }

    $carts = $query->paginate(10)->withQueryString();

    return view('admin.carts.index', compact('carts'));
}

    // View the cart
    public function viewCart()
    {
        $user = Auth::user();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending']
        );

        $cartItems = $cart->items()
            ->when($user->role === UserRole::BUYER, fn ($q) => $q->with('product', 'material'))
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
            // Support adding product or material to cart
            if ($request->has('product_id')) {
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
            } elseif ($request->has('material_id')) {
                $request->validate([
                    'material_id' => 'required|exists:materials,id',
                    'quantity' => 'required|integer|min:1',
                ]);

                $material = Material::findOrFail($request->material_id);

                $cart->items()->create([
                    'material_id' => $material->id,
                    'quantity' => $request->quantity,
                    'price' => 0, // materials may not have a price field; set to 0 or adapt if you add material pricing
                    'status' => 'pending',
                    'type' => 'material',
                ]);
            }
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
                $q->where('status', 'pending')->with('product', 'bid', 'material');
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
                        // Decrement product stock
                        $cart->items()->where('status', 'pending')->with('product')->get()->each(function ($item) {
                            if ($item->type === 'product' && $item->product) {
                                $product = $item->product;
                                $decrement = (int) $item->quantity;
                                $newStock = max(0, $product->stock - $decrement);
                                $product->update(['stock' => $newStock]);
                            }
                        });

                        // Decrement material quantity (units) if material items exist
                        $cart->items()->where('status', 'pending')->with('material')->get()->each(function ($item) {
                            if ($item->type === 'material' && $item->material) {
                                $material = $item->material;
                                $decrement = (float) $item->quantity;
                                // Ensure quantity does not go negative
                                $newQuantity = max(0, $material->quantity - $decrement);
                                $material->update(['quantity' => $newQuantity]);
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
