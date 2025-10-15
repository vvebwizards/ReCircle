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

    <form class="b-filters" method="GET" action="{{ route('dashboard.bids') }}">
      <div class="bf-grid">
        <div class="bf-field">
          <label>Status</label>
          <select name="status">
            <option value="">All</option>
            @foreach(['pending','accepted','rejected','withdrawn'] as $st)
              <option value="{{ $st }}" @if(($filters['status'] ?? '')===$st) selected @endif>{{ ucfirst($st) }}</option>
            @endforeach
          </select>
        </div>
        <div class="bf-field">
          <label>Listing Title</label>
          <input type="text" name="title" value="{{ $filters['title'] ?? '' }}" placeholder="Search listing..." />
        </div>
        <div class="bf-field">
          <label>From</label>
          <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" />
        </div>
        <div class="bf-field">
          <label>To</label>
          <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" />
        </div>
        <div class="bf-field">
          <label>Min Amount</label>
          <input type="number" step="0.01" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}" />
        </div>
        <div class="bf-field">
          <label>Max Amount</label>
          <input type="number" step="0.01" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}" />
        </div>
        <div class="bf-actions">
          <a href="{{ route('dashboard.bids') }}" class="btn secondary sm bf-reset">Reset</a>
        </div>
      </div>
      @php $hasFilters = array_filter([$filters['status'] ?? null,$filters['title'] ?? null,$filters['from'] ?? null,$filters['to'] ?? null,$filters['min_amount'] ?? null,$filters['max_amount'] ?? null], fn($v)=>$v!==''); @endphp
      <div class="bf-chips @if($hasFilters) has-chips @endif">
        @if($filters['status'] ?? false)<button type="button" class="chip" data-clear="status">Status: {{ ucfirst($filters['status']) }} <i class="fa-solid fa-xmark"></i></button>@endif
        @if($filters['title'] ?? false)<button type="button" class="chip" data-clear="title">Title: {{ $filters['title'] }} <i class="fa-solid fa-xmark"></i></button>@endif
        @if($filters['from'] ?? false)<button type="button" class="chip" data-clear="from">From: {{ $filters['from'] }} <i class="fa-solid fa-xmark"></i></button>@endif
        @if($filters['to'] ?? false)<button type="button" class="chip" data-clear="to">To: {{ $filters['to'] }} <i class="fa-solid fa-xmark"></i></button>@endif
        @if($filters['min_amount'] ?? false)<button type="button" class="chip" data-clear="min_amount">Min: {{ $filters['min_amount'] }} <i class="fa-solid fa-xmark"></i></button>@endif
        @if($filters['max_amount'] ?? false)<button type="button" class="chip" data-clear="max_amount">Max: {{ $filters['max_amount'] }} <i class="fa-solid fa-xmark"></i></button>@endif
      </div>
    </form>

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
            $img = $primary ? ($primary->image_url ?? $primary->image_path) : asset('images/default-material.png');
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
                  <li class="bid-row status-{{ $bid->status }}" data-bid-id="{{ $bid->id }}">
                    <div class="row-main">
                      <span class="amt">{{ number_format($bid->amount,2) }} {{ $bid->currency }}</span>
                      <span class="maker">by {{ $bid->maker->name }}</span>
                    </div>
                    <div class="row-meta">
                      <span class="time">{{ $bid->created_at->diffForHumans() }}</span>
                      <span class="pill p-{{ $bid->status }}">{{ strtoupper($bid->status) }}</span>
                    </div>
                    @if(!$accepted && $bid->status==='pending')
                      <div class="row-actions"><button type="button" class="btn-accept-bid" data-accept-bid data-bid-id="{{ $bid->id }}" data-item-id="{{ $item->id }}" data-maker="{{ $bid->maker->name }}" data-amount="{{ number_format($bid->amount,2) }}" data-currency="{{ $bid->currency }}">Accept</button></div>
                    @endif
                  </li>
                @endforeach
                @if($count > 3)
                  <li class="more" data-expand>
                    <button type="button" class="expand-btn" data-expand-trigger>+ {{ $count - 3 }} more</button>
                    <ul class="extra" hidden>
                      @foreach($item->bids->slice(3) as $bid)
                        <li class="bid-row status-{{ $bid->status }}" data-bid-id="{{ $bid->id }}">
                          <div class="row-main">
                            <span class="amt">{{ number_format($bid->amount,2) }} {{ $bid->currency }}</span>
                            <span class="maker">by {{ $bid->maker->name }}</span>
                          </div>
                          <div class="row-meta">
                            <span class="time">{{ $bid->created_at->diffForHumans() }}</span>
                            <span class="pill p-{{ $bid->status }}">{{ strtoupper($bid->status) }}</span>
                          </div>
                          @if(!$accepted && $bid->status==='pending')
                            <div class="row-actions"><button type="button" class="btn-accept-bid" data-accept-bid data-bid-id="{{ $bid->id }}" data-item-id="{{ $item->id }}" data-maker="{{ $bid->maker->name }}" data-amount="{{ number_format($bid->amount,2) }}" data-currency="{{ $bid->currency }}">Accept</button></div>
                          @endif
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
<!-- Fullscreen Accept Confirmation Modal -->
<div class="ac-overlay" id="acceptBidModal" hidden aria-hidden="true" role="dialog" aria-modal="true">
  <div class="ac-modal" role="document">
    <button type="button" class="ac-close" data-ac-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <div class="acm-head">
      <div class="icon-wrap"><i class="fa-solid fa-gavel"></i></div>
      <div class="head-text">
        <h2>Accept this bid?</h2>
        <p class="acm-body">You are about to accept a bid for <span class="ac-target-amt"></span>. This will automatically reject all other pending bids for this listing.</p>
      </div>
    </div>
    <div class="ac-summary" aria-label="Bid summary">
      <div class="row"><span class="lbl">Bidder</span><span class="val ac-s-maker"></span></div>
      <div class="row"><span class="lbl">Amount</span><span class="val ac-s-amt"></span></div>
      <div class="row"><span class="lbl">Listing</span><span class="val ac-s-title"></span></div>
    </div>
    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><span>This action cannot be undone.</span></div>
    <p class="ac-error-msg" hidden aria-live="assertive"></p>
    <div class="acm-actions">
      <button type="button" class="btn-ac-outline" data-ac-close>Cancel</button>
      <button type="button" class="btn-ac-primary" data-ac-confirm-modal>
        <span class="spinner" hidden></span>
        <span class="btn-label">Accept Bid</span>
      </button>
    </div>
  </div>
</div>
@endsection

{{-- Formulaire caché pour PATCH (soumission "classique" => redirection serveur) --}}
<form id="accept-bid-form" method="POST" action="" style="display:none;">
  @csrf
  <input type="hidden" name="_method" value="PATCH">
  <input type="hidden" name="status" value="accepted">
</form>

@push('scripts')
<script>
(function () {
  // --- MODAL ---
  const modal = document.getElementById('acceptBidModal');
  const btnConfirm = modal.querySelector('[data-ac-confirm-modal]');
  const btnCloseSelectors = '[data-ac-close]';
  let activeBidId = null, lastTrigger = null;

  const amtSpan = modal.querySelector('.ac-target-amt');
  const makerSpan = modal.querySelector('.ac-s-maker');
  const sumAmtSpan = modal.querySelector('.ac-s-amt');
  const sumTitleSpan = modal.querySelector('.ac-s-title');

  function openModal(bidRow, triggerBtn){
    activeBidId = bidRow.getAttribute('data-bid-id');
    lastTrigger = triggerBtn;

    const amt = triggerBtn.getAttribute('data-amount');
    const currency = triggerBtn.getAttribute('data-currency');
    const maker = triggerBtn.getAttribute('data-maker');
    const listingTitle = bidRow.closest('.bid-item-card').querySelector('.wi-title').textContent;

amtSpan.textContent = (amt && currency) ? (amt + ' ' + currency) : '—';    makerSpan.textContent = maker || '—';
    sumAmtSpan.textContent = amtSpan.textContent;
    sumTitleSpan.textContent = listingTitle;

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    btnConfirm.focus();
  }

  function closeModal(){
    modal.hidden = true;
    document.body.style.overflow = '';
    if (lastTrigger) lastTrigger.focus();
    activeBidId = null; lastTrigger = null;
  }

  // Ouvrir le modal depuis le bouton "ACCEPT"
  document.addEventListener('click', function (e) {
    const acceptBtn = e.target.closest('[data-accept-bid]');
    if (acceptBtn) {
      const bidRow = acceptBtn.closest('.bid-row');
      openModal(bidRow, acceptBtn);
    }
  });

  // Fermer (croix, Cancel, clic en dehors)
  modal.addEventListener('click', function (e) {
    if (e.target === modal || e.target.matches(btnCloseSelectors)) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });

  // >>> Confirmer = soumettre le formulaire caché (PATCH) => le contrôleur fera la REDIRECTION
  btnConfirm.addEventListener('click', function () {
    if (!activeBidId) return;
    const form = document.getElementById('accept-bid-form');

    // URL basée sur la route nommée 'bids.updateStatus'
    // On insère l'ID dans le placeholder ':id'
    form.action = "{{ route('bids.updateStatus', ':id') }}".replace(':id', activeBidId);

    form.submit(); // -> redirection serveur vers pickups.create
  });
})();
</script>
@endpush
