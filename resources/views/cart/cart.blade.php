<!-- resources/views/cart.blade.php -->
@extends('layouts.app')

@push('head')
@vite([
    'resources/css/materials.css',
    'resources/css/material-create.css',
    'resources/css/waste-items.css',
    'resources/css/market-place.css',
    'resources/js/waste-items.js'
])
@endpush

@section('content')
<main class="cart-page">
    <div class="container">
        <header class="dash-header">
            <div class="dash-hello">
                <h1>Your Cart</h1>
                <p class="dash-sub">Review your selected purchases before checkout.</p>
            </div>
        </header>

        <div class="cart-stack">
            <!-- Sample Cart Item 1 -->
            <section class="dash-card" id="cart-item-1">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-box"></i></div>
                    <h3 class="card-title">Recycled Plastic Pellets</h3>
                    <p class="card-desc">High-quality recycled HDPE pellets.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <span class="chip"><i class="fa-solid fa-tag"></i> $5/kg</span>
                        <span class="chip"><i class="fa-solid fa-cubes"></i> Qty: 10</span>
                        <button class="btn btn-danger"><i class="fa-solid fa-trash"></i> Remove</button>
                    </div>
                </div>
            </section>

            <!-- Sample Cart Item 2 -->
            <section class="dash-card" id="cart-item-2">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-gear"></i></div>
                    <h3 class="card-title">Upcycled Wood Scraps</h3>
                    <p class="card-desc">Perfect for crafting or construction.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <span class="chip"><i class="fa-solid fa-tag"></i> $10/bundle</span>
                        <span class="chip"><i class="fa-solid fa-cubes"></i> Qty: 3</span>
                        <button class="btn btn-danger"><i class="fa-solid fa-trash"></i> Remove</button>
                    </div>
                </div>
            </section>

            <!-- Sample Cart Item 3 -->
            <section class="dash-card" id="cart-item-3">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-recycle"></i></div>
                    <h3 class="card-title">Textile Waste</h3>
                    <p class="card-desc">Assorted fabric scraps for creative reuse.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <span class="chip"><i class="fa-solid fa-tag"></i> $3/kg</span>
                        <span class="chip"><i class="fa-solid fa-cubes"></i> Qty: 5</span>
                        <button class="btn btn-danger"><i class="fa-solid fa-trash"></i> Remove</button>
                    </div>
                </div>
            </section>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary dash-card">
            <div class="card-stack">
                <h3 class="card-title"><i class="fa-solid fa-receipt"></i> Order Summary</h3>
                <div class="summary-details">
                    <p><strong>Subtotal:</strong> $95</p>
                    <p><strong>Taxes:</strong> $5</p>
                    <p><strong>Total:</strong> $100</p>
                </div>
                <div class="summary-actions" style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
                    <button class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</button>
                    <button class="btn btn-primary"><i class="fa-solid fa-credit-card"></i> Proceed to Checkout</button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
