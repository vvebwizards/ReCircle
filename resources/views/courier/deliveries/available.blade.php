@extends('layouts.app')

@section('title', 'Available Pickups')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Available Pickups</h1>
        <form method="GET" action="{{ route('deliveries.pickups') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Search…" class="border rounded px-3 py-1">
            <button class="btn">Search</button>
        </form>
    </div>

    @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
    @endif

    @if($pickups->isEmpty())
        <div class="p-6 border rounded bg-white text-gray-600">
            No available pickups for now.
        </div>
    @else
        <div class="overflow-x-auto bg-white border rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Listing</th>
                        <th class="px-4 py-2">Pickup address</th>
                        <th class="px-4 py-2">Window</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pickups as $p)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $p->id }}</td>
                        <td class="px-4 py-2">
                            {{ $p->wasteItem->title ?? '—' }}
                            <small class="text-gray-400">#{{ $p->waste_item_id }}</small>
                        </td>
                        <td class="px-4 py-2">{{ $p->pickup_address }}</td>
                        <td class="px-4 py-2">
                            <div class="text-xs text-gray-500">Start</div>
                            {{ optional($p->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                            <div class="text-xs text-gray-500 mt-1">End</div>
                            {{ optional($p->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-2">
                            {{-- Sélectionner ce pickup → crée la delivery assignée au livreur --}}
                            <form method="POST" action="{{ route('deliveries.fromPickup.store', $p) }}">
                                @csrf
                                <input type="hidden" name="courier_phone" value="{{ auth()->user()->phone ?? '' }}">
                                <input type="hidden" name="hub_address" value="{{ config('delivery.hub.address','ReCircle Hub — 12 Rue Exemple, Tunis') }}">
                                <input type="hidden" name="hub_lat" value="{{ config('delivery.hub.lat', 36.8065) }}">
                                <input type="hidden" name="hub_lng" value="{{ config('delivery.hub.lng', 10.1815) }}">
                                <button class="px-3 py-1 rounded bg-blue-600 text-white text-xs">
                                    Select delivery
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pickups->links() }}
        </div>
    @endif
</div>
@endsection