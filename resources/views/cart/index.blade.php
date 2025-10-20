@extends('layouts.app')

@push('head')
{{-- Consider using a CSS framework like Tailwind CSS for a truly modern and utility-first approach. --}}
{{-- For this redesign, we assume updated, modern styles in cart.css --}}
@vite([
    'resources/css/cart.css',
    'resources/js/cart.js'
])
@endpush

@section('content')
<div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-8 text-gray-900">
        {{-- Dynamic and clear title based on user role --}}
        @if(auth()->user()?->role === \App\Enums\UserRole::MAKER)
            Accepted Bids Overview
        @elseif(auth()->user()?->role === \App\Enums\UserRole::BUYER)
            Your Shopping Cart
        @else
            Your Items
        @endif
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Cart Items (Takes 2/3 space on large screens) --}}
        <div class="lg:col-span-2 space-y-6">
            @forelse($orders as $item)
                {{-- Modern Card Design: Shadow, Rounded Corners, and Flexbox for layout --}}
                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg flex flex-col md:flex-row items-start md:items-center transition duration-300 hover:shadow-2xl border border-gray-100">

                    {{-- 1. Image & Item Type Badge --}}
                    <div class="relative w-full md:w-32 h-32 flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                        @php
                            $imagePath = 'images/default-image.png'; // Default
                            $itemType = '';
                            $itemTitle = 'Item';

                            if (auth()->user()?->role === \App\Enums\UserRole::BUYER) {
                                if ($item->type === 'product' && $item->product) {
                                    $imagePath = $item->product->images->first()->image_path ?? $imagePath;
                                    $itemType = 'Product';
                                    $itemTitle = $item->product->name;
                                } elseif ($item->type === 'material' && $item->material) {
                                    $imagePath = $item->material->images->first()->image_path ?? $imagePath;
                                    $itemType = 'Material';
                                    $itemTitle = $item->material->name;
                                }
                            } elseif (auth()->user()?->role === \App\Enums\UserRole::MAKER && $item->bid) {
                                $imagePath = $item->bid->wasteItem->primary_image_url ?? $imagePath;
                                $itemType = 'Waste Bid';
                                $itemTitle = $item->bid->wasteItem->title ?? 'Waste Item';
                            }
                        @endphp

                        <img src="{{ asset($imagePath) }}" alt="{{ $itemTitle }}" class="w-full h-full object-cover rounded-lg border border-gray-200">

                        {{-- Item Type Badge (Modern detail) --}}
                        <span class="absolute top-0 right-0 mt-2 mr-2 px-2 py-0.5 text-xs font-medium rounded-full
                            @if($itemType === 'Product') bg-indigo-100 text-indigo-800
                            @elseif($itemType === 'Material') bg-green-100 text-green-800
                            @elseif($itemType === 'Waste Bid') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $itemType }}
                        </span>
                    </div>

                    {{-- 2. Details (Central, taking flexible space) --}}
                    <div class="flex-grow min-w-0 mb-4 md:mb-0">
                        <h2 class="text-lg font-semibold text-gray-800 truncate mb-1">
                            <a href="#" class="hover:text-indigo-600">{{ $itemTitle }}</a>
                        </h2>

                        @if(auth()->user()?->role === \App\Enums\UserRole::BUYER)
                            @php
                                $ownerName = ($item->product->maker?->name ?? $item->material->maker?->name) ?? 'Unknown Creator';
                            @endphp
                            <p class="text-sm text-gray-500 mb-2">Creator: {{ $ownerName }}</p>
                            
                            {{-- Status Badge (More prominent and color-coded) --}}
                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full
                                @php
                                    $status = strtolower($item->status ?? 'pending');
                                @endphp
                                @if($status === 'accepted') bg-green-100 text-green-700
                                @elseif($status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($status === 'rejected') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                Status: {{ ucfirst($status) }}
                            </span>

                        @elseif(auth()->user()?->role === \App\Enums\UserRole::MAKER && $item->bid)
                            <p class="text-sm text-gray-500 mb-2">Owner: {{ $item->bid->wasteItem->generator?->name ?? 'Unknown Owner' }}</p>
                            @if($item->bid->wasteItem->notes)
                                <p class="text-sm text-gray-600 italic mt-2">Notes: {{ Str::limit($item->bid->wasteItem->notes, 70) }}</p>
                            @endif
                            <p class="text-sm text-indigo-600 font-medium mt-2">Your Accepted Bid Price: ${{ number_format($item->price, 2) }}</p>
                        @endif
                    </div>

                    {{-- 3. Pricing & Actions (Aligned to the right on desktop, clear stacking on mobile) --}}
                    {{-- In cart/index.blade.php, inside the @forelse loop --}}
                    <div class="text-right mt-4 md:mt-0 md:ml-6 w-full md:w-auto flex flex-col space-y-2 border-t md:border-t-0 pt-4 md:pt-0">
                        @php
                            $linePrice = (float) $item->price * (float) $item->quantity;
                        @endphp

                        {{-- ... (keep pricing details) ... --}}
                        <div class="flex justify-between items-center text-sm text-gray-700">
                            <span class="font-medium">Price/Unit:</span>
                            <span class="font-bold text-gray-900">${{ number_format($item->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold text-gray-900 border-t pt-2 mt-2">
                            <span>Total:</span>
                            <span>${{ number_format($linePrice, 2) }}</span>
                        </div>

                        {{-- ACTION: Remove Button (Using a form for DELETE request) --}}
                        <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                            @csrf
                            {{-- Laravel Blade directive for spoofing DELETE method --}}
                            @method('DELETE') 
                            
                            {{-- Disable removal for items that are not in a 'pending' state --}}
                            @if(strtolower($item->status ?? 'pending') === 'pending')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium mt-3 transition duration-150 w-full">
                                    Remove
                                </button>
                            @else
                                {{-- Display a disabled state for non-removable items --}}
                                <span class="text-gray-400 text-sm mt-3 w-full inline-block text-center cursor-not-allowed">
                                    Item Processed
                                </span>
                            @endif
                        </form>
                    </div>
                </div>
            @empty
                {{-- Empty State Card --}}
                <div class="bg-white p-10 rounded-xl shadow-lg text-center border-2 border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-4 text-xl text-gray-600">
                        @if(auth()->user()?->role === \App\Enums\UserRole::MAKER)
                            No accepted bids yet. Keep bidding on waste items!
                        @else
                            Your cart is empty. Start shopping for amazing recycled products!
                        @endif
                    </p>
                    <a href="{{ route('marketplace.index') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Browse Items
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Right Column: Summary & Actions (Fixed width or 1/3 space) --}}
        <div class="lg:col-span-1 sticky top-8 h-fit">
            <div class="bg-white p-6 rounded-xl shadow-xl border border-gray-100">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6 border-b pb-3">Order Summary</h3>

                <div class="space-y-3">
                    @php
                        $subtotalAmount = $orders->reduce(fn ($carry, $item) => $carry + ((float) $item->price * (float) $item->quantity), 0);
                    @endphp

                    {{-- Subtotal Line --}}
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal ({{ $orders->count() }} items):</span>
                        <span>$<span id="subtotal" data-amount="{{ $subtotalAmount }}">{{ number_format($subtotalAmount, 2) }}</span></span>
                    </div>

                    {{-- Discount Line (Lighter color to show secondary data) --}}
                    <div class="flex justify-between text-gray-500">
                        <span>Discount/Promo:</span>
                        <span>-$<span id="discount">0.00</span></span>
                    </div>
                    
                    {{-- Separator Line --}}
                    <div class="border-t border-gray-200 pt-3"></div>

                    {{-- Grand Total Line (Bold and prominent) --}}
                    <div class="flex justify-between text-xl font-bold text-gray-900">
                        <strong><span>Order Total:</span></strong>
                        <strong><span>$<span id="total">{{ number_format($subtotalAmount, 2) }}</span></span></strong>
                    </div>
                </div>

                {{-- Checkout Action --}}
                @if($orders->isNotEmpty())
                    <form action="{{ route('cart.checkout') }}" method="POST" class="mt-6">
                        @csrf
                        {{-- Modern Button: Full width, prominent color, hover effect --}}
                        <button type="submit" class="w-full py-3 bg-indigo-600 text-white text-lg font-semibold rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Proceed to Checkout
                        </button>
                    </form>
                @else
                    <button disabled class="w-full py-3 bg-gray-400 text-white text-lg font-semibold rounded-lg cursor-not-allowed">
                        Total $0.00
                    </button>
                @endif
            </div>

            {{-- Summary Note (Below the card, subtle) --}}
            <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm text-gray-600 border border-gray-200">
                <p><strong>Note:</strong> Orders containing bids must be paid within 48 hours. Items cannot be modified after payment.</p>
            </div>
        </div>
    </div>
</div>
@endsection