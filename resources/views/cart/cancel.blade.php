@extends('layouts.app')

@push('head')
@vite(['resources/css/cart.css'])
<style>
.cancel-card {
    max-width: 500px;
    margin: 60px auto;
    padding: 40px;
    background-color: #FEE2E2; /* Danger red */
    border: 1px solid #EF4444;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    animation: fadeIn 0.6s ease-out;
}

.cancel-card h1 {
    font-size: 28px;
    color: #B91C1C;
    margin-bottom: 15px;
}

.cancel-card p {
    font-size: 16px;
    color: var(--text-color-primary);
    margin-bottom: 25px;
}

.cancel-card a {
    display: inline-block;
    padding: 12px 25px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.2s, transform 0.1s;
}

.cancel-card a:hover {
    background-color: #2563EB;
    transform: translateY(-2px);
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="cancel-card">
        <h1>❌ Payment Cancelled</h1>
        <p>Your payment was cancelled. Your cart was not charged.</p>
        <a href="/cart">Return to Cart</a>
    </div>
</div>
@endsection
