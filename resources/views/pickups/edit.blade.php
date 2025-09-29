@extends('layouts.app')

@section('title', 'Edit Pickup')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-2xl font-semibold mb-6">Edit Pickup</h1>

    <form method="POST" action="{{ route('pickups.update', $pickup->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-medium mb-1">Pickup address *</label>
            <input name="pickup_address" value="{{ old('pickup_address', $pickup->pickup_address) }}" class="w-full border rounded p-2">
            @error('pickup_address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium mb-1">Window start</label>
                <input type="datetime-local" name="scheduled_pickup_window_start"
                       value="{{ old('scheduled_pickup_window_start', $pickup->scheduled_pickup_window_start ? $pickup->scheduled_pickup_window_start->format('Y-m-d\TH:i') : '') }}"
                       class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block font-medium mb-1">Window end</label>
                <input type="datetime-local" name="scheduled_pickup_window_end"
                       value="{{ old('scheduled_pickup_window_end', $pickup->scheduled_pickup_window_end ? $pickup->scheduled_pickup_window_end->format('Y-m-d\TH:i') : '') }}"
                       class="w-full border rounded p-2">
            </div>
        </div>

        <div>
            <label class="block font-medium mb-1">Status *</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['scheduled','assigned','in_transit','picked','failed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $pickup->status)===$s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-medium mb-1">Notes</label>
            <textarea name="notes" rows="4" class="w-full border rounded p-2">{{ old('notes', $pickup->notes) }}</textarea>
        </div>

        <button class="bg-green-700 text-white px-4 py-2 rounded">Update Pickup</button>
    </form>
</div>
@endsection
