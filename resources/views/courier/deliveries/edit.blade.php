@extends('layouts.app')
@section('title', 'Edit Delivery')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <h1 class="text-xl font-semibold mb-4">Edit delivery #{{ $delivery->id }}</h1>

    <div class="mb-4 p-4 rounded border bg-white">
        <div class="text-sm text-gray-600">
            <div><strong>Pickup #{{ $delivery->pickup_id }}</strong> — {{ $delivery->pickup->wasteItem->title ?? '—' }}</div>
            <div>Address: {{ $delivery->pickup->pickup_address ?? '—' }}</div>
            <div>Window: 
                {{ optional($delivery->pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
                →
                {{ optional($delivery->pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
            </div>
        </div>
    </div>

    @if(empty($allowed))
        <div class="p-4 rounded bg-amber-50 text-amber-800 border">
            This delivery can no longer be edited (status: {{ $delivery->status }}).
        </div>
        <div class="mt-4">
            <a href="{{ route('deliveries.index') }}" class="px-4 py-2 rounded bg-slate-200">Back</a>
        </div>
    @else
        <form method="POST" action="{{ route('deliveries.update', $delivery) }}" class="bg-white border rounded p-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    @foreach($allowed as $s)
                        <option value="{{ $s }}" @selected($delivery->status === $s)>
                            {{ ucfirst(str_replace('_',' ', $s)) }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Courier phone</label>
                <input type="text" name="courier_phone" value="{{ old('courier_phone', $delivery->courier_phone) }}" class="w-full border rounded px-3 py-2">
                @error('courier_phone') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes', $delivery->notes) }}</textarea>
                @error('notes') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
                <a href="{{ route('deliveries.index') }}" class="px-4 py-2 rounded bg-slate-200">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
