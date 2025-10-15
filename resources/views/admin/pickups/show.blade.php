@extends('layouts.admin')

@section('title', 'Pickup #'.$pickup->id)

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Pickup #{{ $pickup->id }}</h1>
    <div class="tb-sub">Details for this pickup</div>
  </div>
  <div class="tb-right">
    <a href="{{ route('admin.pickups.index') }}" class="btn sm">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    {{-- on réutilise le formulaire d’édition “général” --}}
    <a href="{{ route('pickups.edit', $pickup) }}" class="btn sm is-blue">
      <i class="fa-solid fa-pen"></i> Edit
    </a>
    <form method="POST" action="{{ route('pickups.destroy', $pickup) }}" onsubmit="return confirm('Delete this pickup?');" style="display:inline">
      @csrf @method('DELETE')
      <button class="btn sm is-red">
        <i class="fa-solid fa-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

<section class="admin-grid">
  <div class="a-card wide">
    <div class="a-title"><i class="fa-solid fa-truck"></i> Pickup summary</div>

    <div class="a-rows">
      <div class="a-row">
        <span class="a-label">Listing</span>
        <span class="a-value">
          {{ $pickup->wasteItem->title ?? '—' }}
          <small class="text-gray-500"> (#{{ $pickup->waste_item_id }})</small>
        </span>
      </div>

      <div class="a-row">
        <span class="a-label">Address</span>
        <span class="a-value">{{ $pickup->pickup_address }}</span>
      </div>

      <div class="a-row">
        <span class="a-label">Window</span>
        <span class="a-value">
          {{ optional($pickup->scheduled_pickup_window_start)->format('Y-m-d H:i') ?? '—' }}
          &nbsp;→&nbsp;
          {{ optional($pickup->scheduled_pickup_window_end)->format('Y-m-d H:i') ?? '—' }}
        </span>
      </div>

      <div class="a-row">
        <span class="a-label">Status</span>
        <span class="a-value">
          <span class="badge
              @if($pickup->status==='scheduled')  is-amber
              @elseif($pickup->status==='assigned') is-indigo
              @elseif($pickup->status==='in_transit') is-blue
              @elseif($pickup->status==='picked')    is-green
              @elseif($pickup->status==='failed')    is-red
              @else                                  is-gray
              @endif">
            {{ ucfirst(str_replace('_',' ', $pickup->status)) }}
          </span>
        </span>
      </div>

      <div class="a-row">
        <span class="a-label">Tracking</span>
        <span class="a-value">{{ $pickup->tracking_code ?? '—' }}</span>
      </div>

      <div class="a-row">
        <span class="a-label">Courier</span>
        <span class="a-value">{{ $pickup->courier_id ? '#'.$pickup->courier_id : '—' }}</span>
      </div>

      <div class="a-row">
        <span class="a-label">Notes</span>
        <span class="a-value whitespace-pre-line">{{ $pickup->notes ?? '—' }}</span>
      </div>

      <div class="a-row two">
        <div>
          <div class="a-label">Created</div>
          <div class="a-value">{{ $pickup->created_at }}</div>
        </div>
        <div>
          <div class="a-label">Updated</div>
          <div class="a-value">{{ $pickup->updated_at }}</div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection