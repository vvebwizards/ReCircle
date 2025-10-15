@extends('layouts.admin')

@section('title','Edit Pickup #'.$pickup->id)

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Edit Pickup</h1>
    <div class="tb-sub">Update details for pickup #{{ $pickup->id }}</div>
  </div>
</div>

<section class="admin-grid">
  <div class="a-card">
    <div class="a-title"><i class="fa-solid fa-truck"></i> Pickup #{{ $pickup->id }}</div>

    <form method="POST" action="{{ route('admin.pickups.update', $pickup) }}" class="a-form">
      @csrf
      @method('PUT')

      <div class="form-row">
        <label>Listing</label>
        <div class="pill">{{ $pickup->wasteItem->title ?? '—' }} <span class="muted">#{{ $pickup->waste_item_id }}</span></div>
      </div>

      <div class="form-row">
        <label>Pickup address *</label>
        <input name="pickup_address" value="{{ old('pickup_address',$pickup->pickup_address) }}" required>
        @error('pickup_address') <div class="err">{{ $message }}</div> @enderror
      </div>

      <div class="form-row two">
        <div>
          <label>Window start</label>
          <input type="datetime-local"
                 name="scheduled_pickup_window_start"
                 value="{{ old('scheduled_pickup_window_start', optional($pickup->scheduled_pickup_window_start)->format('Y-m-d\TH:i')) }}">
          @error('scheduled_pickup_window_start') <div class="err">{{ $message }}</div> @enderror
        </div>
        <div>
          <label>Window end</label>
          <input type="datetime-local"
                 name="scheduled_pickup_window_end"
                 value="{{ old('scheduled_pickup_window_end', optional($pickup->scheduled_pickup_window_end)->format('Y-m-d\TH:i')) }}">
          @error('scheduled_pickup_window_end') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="form-row two">
        <div>
          <label>Status *</label>
          <select name="status">
            @foreach (['scheduled','assigned','in_transit','picked','failed','cancelled'] as $s)
              <option value="{{ $s }}" @selected(old('status',$pickup->status)===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
          </select>
          @error('status') <div class="err">{{ $message }}</div> @enderror
        </div>
        <div>
          <label>Tracking code</label>
          <input name="tracking_code" value="{{ old('tracking_code',$pickup->tracking_code) }}">
          @error('tracking_code') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="form-row two">
        <div>
          <label>Courier (id)</label>
          <input type="number" name="courier_id" value="{{ old('courier_id',$pickup->courier_id) }}">
          @error('courier_id') <div class="err">{{ $message }}</div> @enderror
        </div>
        <div>
          <label>Notes</label>
          <textarea name="notes" rows="3">{{ old('notes',$pickup->notes) }}</textarea>
          @error('notes') <div class="err">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="form-actions">
        <a href="{{ url()->previous() ?: route('admin.pickups.index') }}" class="btn is-ghost">Cancel</a>
        <button class="btn is-blue">Save changes</button>
      </div>
    </form>
  </div>
</section>
@endsection
