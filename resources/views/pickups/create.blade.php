@extends('layouts.app')

@section('title','Create Pickup')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">

    @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
    @endif

    <h1 class="text-2xl font-semibold mb-6">Create Pickup</h1>

    <form method="POST" action="{{ route('pickups.store') }}" class="space-y-5">
        @csrf

        {{-- waste item pré-sélectionné et caché --}}
        <input type="hidden" name="waste_item_id" value="{{ optional($wasteItem)->id }}">

        @if($wasteItem)
            <div class="p-3 rounded border bg-gray-50 text-sm">
                <strong>Waste item:</strong> #{{ $wasteItem->id }} — {{ $wasteItem->title ?? 'N/A' }}
            </div>
        @endif

        <div>
            <label class="block font-medium mb-1">Pickup address *</label>
            <input name="pickup_address" value="{{ old('pickup_address') }}"
                   class="w-full border rounded p-2" required>
            @error('pickup_address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium mb-1">Window start</label>
                <input type="datetime-local" name="scheduled_pickup_window_start"
                       value="{{ old('scheduled_pickup_window_start') }}"
                       class="w-full border rounded p-2">
                @error('scheduled_pickup_window_start') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block font-medium mb-1">Window end</label>
                <input type="datetime-local" name="scheduled_pickup_window_end"
                       value="{{ old('scheduled_pickup_window_end') }}"
                       class="w-full border rounded p-2">
                @error('scheduled_pickup_window_end') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block font-medium mb-1">Status *</label>
            <select name="status" class="w-full border rounded p-2">
                @foreach (['scheduled','assigned','in_transit','picked','failed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status','scheduled')===$s)>
                        {{ ucfirst(str_replace('_',' ', $s)) }}
                    </option>
                @endforeach
            </select>
            @error('status') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block font-medium mb-1">Notes</label>
            <textarea name="notes" rows="4" class="w-full border rounded p-2">{{ old('notes') }}</textarea>
            @error('notes') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <button class="bg-green-700 text-white px-4 py-2 rounded">Save Pickup</button>
    </form>
</div>
@endsection
