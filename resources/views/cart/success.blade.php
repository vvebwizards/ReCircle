@extends('layouts.app')

@push('head')
@vite(['resources/css/cart.css'])
<style>
.success-card {
    max-width: 500px;
    margin: 60px auto;
    padding: 40px;
    background-color: #D1FAE5; /* Success green */
    border: 1px solid #10B981;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    animation: fadeIn 0.6s ease-out;
}

.success-card h1 {
    font-size: 28px;
    color: #065F46;
    margin-bottom: 15px;
}

.success-card p {
    font-size: 16px;
    color: var(--text-color-primary);
    margin-bottom: 25px;
}

.success-card a {
    display: inline-block;
    padding: 12px 25px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.2s, transform 0.1s;
}

.success-card a:hover {
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
    <div class="success-card">
        <h1>✅ Payment Successful</h1>
        <p>Thank you for your purchase! Your cart has been marked as paid.</p>
        <a href="/">Return to Home</a>
    </div>
</div>
@endsection
