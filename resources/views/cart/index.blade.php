@extends('layouts.app')

@push('head')
@vite([
    'resources/css/cart.css',
    'resources/js/cart.js'
])
@endpush

@section('content')
<div class="page-container">
    <h1 class="page-title">
        @if(auth()->user()?->role === \App\Enums\UserRole::MAKER)
            Accepted Bids
        @elseif(auth()->user()?->role === \App\Enums\UserRole::BUYER)
            Your Cart
        @endif
    </h1>

    <div class="cart-layout">
        {{-- Left Column: Cart Items --}}
        <div class="cart-items-section">
            @forelse($orders as $item)
                <div class="cart-card">
                    {{-- Image --}}
                    <div class="item-image" style="margin-right: 15px;">
                        @if(auth()->user()?->role === \App\Enums\UserRole::BUYER)
                            @if($item->product?->images && count($item->product->images) > 0)
                                <img src="{{ asset($item->product->images->first()->image_path) }}" alt="Product Image">
                            @elseif($item->material?->images && count($item->material->images) > 0)
                                <img src="{{ asset($item->material->images->first()->image_path) }}" alt="Material Image">
                            @else
                                <img src="{{ asset('images/default-product.png') }}" alt="Item Image">
                            @endif
                        @elseif(auth()->user()?->role === \App\Enums\UserRole::MAKER && $item->bid?->wasteItem?->primary_image_url)
                            <img src="{{ $item->bid->wasteItem->primary_image_url ?? asset('images/default-material.png') }}" alt="Waste Item Image">
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="item-details">
                        @if(auth()->user()?->role === \App\Enums\UserRole::BUYER)
                            @if($item->type === 'product' && $item->product)
                                <h2 class="item-name">{{ $item->product->name }}</h2>
                                <p class="item-meta">By: {{ $item->product->maker?->name ?? 'Unknown Maker' }}</p>
                            @elseif($item->type === 'material' && $item->material)
                                <h2 class="item-name">{{ $item->material->name }}</h2>
                                <p class="item-meta">By: {{ $item->material->maker?->name ?? 'Unknown Maker' }}</p>
                            @else
                                <h2 class="item-name">Item</h2>
                            @endif

                            <p class="item-status status-{{ strtolower($item->status ?? 'pending') }}">
                                Status: {{ ucfirst($item->status ?? 'Pending') }}
                            </p>
                        @elseif(auth()->user()?->role === \App\Enums\UserRole::MAKER && $item->bid)
                            <h2 class="item-name">{{ $item->bid->wasteItem->title ?? 'Waste Item' }}</h2>
                            <p class="item-owner">Owner: {{ $item->bid->wasteItem->generator?->name ?? 'Unknown' }}</p>
                            @if($item->bid->wasteItem->notes)
                                <p class="item-notes">{{ $item->bid->wasteItem->notes }}</p>
                            @endif
                        @endif
                    </div>

                    {{-- Pricing --}}
                    <div class="item-pricing">
                        <p class="item-qty">Qty: <strong>{{ $item->quantity }}</strong></p>
                        @php
                            $linePrice = (float) $item->price * (float) $item->quantity;
                        @endphp
                        <p class="item-price">Price: <strong>${{ number_format($item->price, 2) }}</strong></p>
                        <p class="item-subtotal">Subtotal: <strong>${{ number_format($linePrice, 2) }}</strong></p>
                    </div>
                </div>
            @empty
                <p class="empty-message">
                    @if(auth()->user()?->role === \App\Enums\UserRole::MAKER)
                        No accepted bids yet.
                    @else
                        Your cart is empty. Start shopping!
                    @endif
                </p>
            @endforelse
        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="order-summary-section">
            <div class="summary-card">
                <h3 class="summary-title">Cart Summary</h3>

                <div class="summary-totals">
                    @php
                        $subtotalAmount = $orders->reduce(function ($carry, $item) {
                            return $carry + ((float) $item->price * (float) $item->quantity);
                        }, 0);
                    @endphp
                    <div class="total-line">
                        <span>Subtotal:</span>
                        <span>$<span id="subtotal" data-amount="{{ $subtotalAmount }}">{{ number_format($subtotalAmount, 2) }}</span></span>
                    </div>
                    <div class="total-line discount-line">
                        <span>Discount:</span>
                        <span>-$<span id="discount">0.00</span></span>
                    </div>
                    <hr>
                    <div class="total-line grand-total-line">
                        <strong><span>Total:</span></strong>
                        <strong><span>$<span id="total">{{ number_format($subtotalAmount, 2) }}</span></span></strong>
                    </div>
                </div>

                @if($orders->isNotEmpty())
                    <form action="{{ route('cart.checkout') }}" method="POST" class="checkout-form">
                        @csrf
                        <button type="submit" class="place-order-btn">Proceed to Checkout</button>
                    </form>
                @endif
            </div>

            <div class="summary-note">
                <p><strong>Note:</strong> Review your items before checkout. Orders cannot be modified after payment.</p>
            </div>
        </div>
    </div>
</div>
@endsection
