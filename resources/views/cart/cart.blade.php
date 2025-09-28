@extends('layouts.app')

@push('head')
<style>
/* Cart page layout (mirroring settings page) */
.cart {
    padding: 8rem 0 4rem;
    background: var(--color-off-white);
    min-height: 100vh;
}

/* Horizontal cart cards: responsive grid */
.cart-stack {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(3, minmax(280px, 1fr));
    align-items: stretch;
    margin-bottom: 4rem;
}
@media (max-width: 1100px) {
    .cart-stack {
        grid-template-columns: repeat(2, minmax(260px, 1fr));
    }
}
@media (max-width: 700px) {
    .cart-stack {
        grid-template-columns: 1fr;
    }
}
.cart-stack .dash-card {
    height: 100%;
}

/* Add breathing room before footer */
.cart .container {
    padding-bottom: 6rem;
}
@media (min-width: 1024px) {
    .cart .container {
        padding-bottom: 8rem;
    }
}

/* Card polish (consistent with settings page) */
.cart .dash-card {
    transition: box-shadow 0.2s ease, transform 0.12s ease;
}
.cart .dash-card:hover {
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}
.cart .dash-card:active {
    transform: translateY(0);
}
.cart .dash-card .card-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
}
.cart .dash-card .card-icon {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background: #eaf5ef;
    color: #1f7a4a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cart .dash-card:hover .card-icon {
    transform: scale(1.08) rotate(-6deg);
    box-shadow: 0 6px 14px rgba(31, 122, 74, 0.15);
}
.cart .dash-card .card-title {
    margin: 0;
    font-size: 1.15rem;
    color: #184a2f;
}
.cart .dash-card .card-desc {
    margin: 0;
    color: #4b5563;
    line-height: 1.45;
}
.cart .dash-card .card-actions {
    margin-top: 0.5rem;
}

/* Chip styles (for price and quantity) */
.cart .chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: #eef3ee;
    color: #1f5f3e;
    font-weight: 600;
}

/* Button styles */
.cart .btn.btn-danger {
    background: #b42318;
    border: none;
    color: #fff;
    border-radius: 999px;
    padding: 0.7rem 1.1rem;
    box-shadow: 0 4px 14px rgba(180, 35, 24, 0.22);
    transition: transform 0.08s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.cart .btn.btn-danger:hover {
    background: #9f1f15;
    box-shadow: 0 6px 18px rgba(180, 35, 24, 0.28);
    transform: translateY(-1px);
}
.cart .btn.btn-danger:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(180, 35, 24, 0.2);
}
.cart .btn.btn-danger:focus {
    outline: 2px solid #f87171;
    outline-offset: 2px;
}

/* Empty cart message */
.cart .empty-cart {
    text-align: center;
    color: #4b5563;
    font-size: 1.1rem;
    margin-top: 2rem;
}

/* Header styles (matching settings page) */
.cart .dash-header {
    margin-bottom: 2rem;
}
.cart .dash-hello h1 {
    font-size: 1.8rem;
    color: #184a2f;
    margin: 0;
}
.cart .dash-hello .dash-sub {
    color: #4b5563;
    font-size: 1rem;
    margin: 0.5rem 0 0;
}
</style>
@endpush

@section('content')
<main class="cart">
    <div class="container">
        <header class="dash-header">
            <div class="dash-hello">
                <h1>Your Cart</h1>
                <p class="dash-sub">Review and manage your selected items.</p>
            </div>
        </header>

        <div class="cart-stack">
            @forelse($orders as $order)
                <section class="dash-card" id="cart-item-{{ $order->id }}">
                    <div class="card-stack">
                        <div class="card-icon"><i class="fa-solid fa-box"></i></div>
                        <h3 class="card-title">{{ $order->product->name ?? 'Product' }}</h3>
                        <p class="card-desc">{{ $order->product->description ?? '' }}</p>
                        <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                            <span class="chip"><i class="fa-solid fa-tag"></i> ${{ number_format($order->unit_price, 2) }}/kg</span>
                            <span class="chip"><i class="fa-solid fa-cubes"></i> Qty: {{ $order->quantity }}</span>
                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Remove</button>
                            </form>
                        </div>
                    </div>
                </section>
            @empty
                <p class="empty-cart">Your cart is empty.</p>
            @endforelse
        </div>
    </div>
</main>
@endsection