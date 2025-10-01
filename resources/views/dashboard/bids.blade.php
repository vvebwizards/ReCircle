@extends('layouts.app')

@push('head')
@vite(['resources/css/dashboard-bids.css'])
@endpush

@section('content')
<main class="dash-bids">
  <div class="container">
    <header class="db-header">
      <h1><i class="fa-solid fa-gavel"></i> Your Bids Received</h1>
      <p class="db-sub">Listings with at least one bid. Top 3 shown; expand to view the rest. Sorted by highest amount.</p>
    </header>

    @if($wasteItems->isEmpty())
      <div class="empty-state">
        <p class="title">No bids yet</p>
        <p class="hint">When bids arrive, they will appear here grouped by listing.</p>
        <a href="{{ route('generator.waste-items.index') }}" class="btn primary sm"><i class="fa-solid fa-plus"></i> New Listing</a>
      </div>
    @else
      <div class="bids-grid">
        @foreach($wasteItems as $item)
          @php
            $accepted = $item->bids->firstWhere('status','accepted');
            $primary = $item->photos->first();
            $img = $primary->image_url ?? $primary->image_path ?? asset('images/default-material.png');
            $count = $item->bids->count();
          @endphp
          <div class="bid-item-card" data-item-id="{{ $item->id }}">
            <div class="card-media">
              <img src="{{ $img }}" alt="{{ $item->title }} image" loading="lazy" />
              <span class="badge total">{{ $count }} {{ Str::plural('Bid',$count) }}</span>
              @if($accepted)
                <span class="badge accepted">Accepted {{ number_format($accepted->amount,2) }} {{ $accepted->currency }}</span>
              @endif
            </div>
            <div class="card-body">
              <h3 class="wi-title">{{ $item->title }}</h3>
              <div class="meta">Updated {{ $item->updated_at?->diffForHumans() }}</div>
              <ul class="bid-rows">
                @php $top = $item->bids->take(3); @endphp
                @foreach($top as $bid)
                  <li class="bid-row status-{{ $bid->status }}">
                    <div class="row-main">
                      <span class="amt">{{ number_format($bid->amount,2) }} {{ $bid->currency }}</span>
                      <span class="maker">by {{ $bid->maker->name }}</span>
                    </div>
                    <div class="row-meta">
                      <span class="time">{{ $bid->created_at->diffForHumans() }}</span>
                      <span class="pill p-{{ $bid->status }}">{{ strtoupper($bid->status) }}</span>
                    </div>
                  </li>
                @endforeach
                @if($count > 3)
                  <li class="more" data-expand>
                    <button type="button" class="expand-btn" data-expand-trigger>+ {{ $count - 3 }} more</button>
                    <ul class="extra" hidden>
                      @foreach($item->bids->slice(3) as $bid)
                        <li class="bid-row status-{{ $bid->status }}">
                          <div class="row-main">
                            <span class="amt">{{ number_format($bid->amount,2) }} {{ $bid->currency }}</span>
                            <span class="maker">by {{ $bid->maker->name }}</span>
                          </div>
                          <div class="row-meta">
                            <span class="time">{{ $bid->created_at->diffForHumans() }}</span>
                            <span class="pill p-{{ $bid->status }}">{{ strtoupper($bid->status) }}</span>
                          </div>
                        </li>
                      @endforeach
                    </ul>
                  </li>
                @endif
              </ul>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</main>
@endsection

@push('scripts')
<script>
  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-expand-trigger]');
    if(!btn) return;
    const wrap = btn.closest('[data-expand]');
    const list = wrap.querySelector('.extra');
    if(list.hidden){ list.hidden=false; btn.textContent='Show less'; }
    else { list.hidden=true; btn.textContent='+'+list.children.length+' more'; }
  });
</script>
@endpush
