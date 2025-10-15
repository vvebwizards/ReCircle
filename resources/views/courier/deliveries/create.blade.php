@extends('layouts.app')

@section('title', 'Select Delivery')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-2xl font-semibold mb-6">Select Delivery</h1>

    <div class="mb-4 p-4 border rounded bg-white">
        <div class="text-sm text-gray-600 mb-2">
            Pickup #{{ $pickup->id }} — <strong>{{ $pickup->wasteItem->title ?? '—' }}</strong>
        </div>
        <div class="text-sm">Address: {{ $pickup->pickup_address }}</div>
        <div class="text-sm text-gray-600 mt-1">
            Window:
            {{ optional($pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
            →
            {{ optional($pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
        </div>
    </div>

    <form method="POST" action="{{ route('deliveries.storeFromPickup', $pickup) }}" class="bg-white border rounded p-4">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Courier phone *</label>
            <input type="text" name="courier_phone" value="{{ old('courier_phone', $defaults['courier_phone']) }}"
                   class="w-full border rounded px-3 py-2" required>
            @error('courier_phone') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Hub address *</label>
            <input type="text" name="hub_address" value="{{ old('hub_address', $defaults['hub_address']) }}"
                   class="w-full border rounded px-3 py-2" required>
            @error('hub_address') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Hub lat</label>
                <input type="number" step="0.0000001" name="hub_lat" value="{{ old('hub_lat', $defaults['hub_lat']) }}"
                       class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Hub lng</label>
                <input type="number" step="0.0000001" name="hub_lng" value="{{ old('hub_lng', $defaults['hub_lng']) }}"
                       class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        <label class="inline-flex items-center mb-4">
            <input type="checkbox" name="start_now" value="1" class="mr-2">
            <span>Start delivery now (set status to <strong>in_transit</strong>)</span>
        </label>

        <div class="flex gap-2">
            <button class="px-4 py-2 rounded bg-green-600 text-white">Save</button>
            <a href="{{ route('pickups.index') }}" class="px-4 py-2 rounded border">Cancel</a>
        </div>
    </form>
</div>
@endsection