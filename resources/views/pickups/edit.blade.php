@extends('layouts.app')
@section('title','Edit Pickup #'.$pickup->id)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
  @if(session('success'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
  @endif

  <h1 class="text-2xl font-semibold mb-6">Edit Pickup #{{ $pickup->id }}</h1>

  <div class="p-3 rounded border bg-gray-50 text-sm mb-4">
      <strong>Listing:</strong> {{ $pickup->wasteItem->title ?? '—' }}
  </div>

  <form method="POST" action="{{ route('pickups.update', $pickup) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <div>
      <label class="block font-medium mb-1">Pickup address *</label>
      <input name="pickup_address" value="{{ old('pickup_address', $pickup->pickup_address) }}"
             class="w-full border rounded p-2" required>
      @error('pickup_address') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-medium mb-1">Window start</label>
        <input type="datetime-local" name="scheduled_pickup_window_start"
               value="{{ old('scheduled_pickup_window_start', optional($pickup->scheduled_pickup_window_start)->format('Y-m-d\TH:i')) }}"
               class="w-full border rounded p-2">
        @error('scheduled_pickup_window_start') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block font-medium mb-1">Window end</label>
        <input type="datetime-local" name="scheduled_pickup_window_end"
               value="{{ old('scheduled_pickup_window_end', optional($pickup->scheduled_pickup_window_end)->format('Y-m-d\TH:i')) }}"
               class="w-full border rounded p-2">
        @error('scheduled_pickup_window_end') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      </div>
    </div>

    <div>
      <label class="block font-medium mb-1">Status *</label>
      <select name="status" class="w-full border rounded p-2">
        @foreach ($statuses as $s)
          <option value="{{ $s }}" @selected(old('status',$pickup->status)===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
      </select>
      @error('status') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block font-medium mb-1">Notes</label>
      <textarea name="notes" rows="4" class="w-full border rounded p-2">{{ old('notes', $pickup->notes) }}</textarea>
      @error('notes') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-3">
      <a href="{{ route('pickups.show', $pickup) }}" class="px-4 py-2 rounded border">Cancel</a>
      <button class="bg-indigo-600 text-white px-4 py-2 rounded">Save changes</button>
    </div>
  </form>
</div>
@endsection
