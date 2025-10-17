@extends('layouts.app')

@push('head')
@vite(['resources/css/dashboard-bids.css', 'resources/css/dashboard.css'])
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
          <div class="bid-item-card" data-item-id="{{ $item->id }}" data-waste-item-id="{{ $item->id }}">
            <div class="card-media">
              <img src="{{ $img }}" alt="{{ $item->title }} image" loading="lazy" />
              <span class="badge total bid-counter">{{ $count }} {{ Str::plural('Bid',$count) }}</span>
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

@push('scripts')
@vite(['resources/js/dashboardBids.js', 'resources/js/bidSocket.js'])
<script>
  // Auto-submit on filter changes with debounce
  let debounceTimer;
  const form = document.querySelector('.b-filters');
  
  function submitForm() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      form.submit();
    }, 500);
  }
  
  // Auto-submit for all filter inputs
  document.querySelectorAll('.b-filters input, .b-filters select').forEach(input => {
    input.addEventListener('input', submitForm);
    input.addEventListener('change', submitForm);
  });
  
  // Filter chip clearing
  document.addEventListener('click', e => {
    const chip = e.target.closest('[data-clear]');
    if(!chip) return;
    const field = chip.getAttribute('data-clear');
    if(form){ const input = form.querySelector(`[name="${field}"]`); if(input){ input.value=''; form.submit(); } }
  });
  (function(){
    const modal = document.getElementById('acceptBidModal');
    let activeBidRow = null;
    let activeBidId = null;
  const amtSpan = modal.querySelector('.ac-target-amt');
    const errorMsg = modal.querySelector('.ac-error-msg');
    const confirmBtn = modal.querySelector('[data-ac-confirm-modal]');
  const spinner = confirmBtn.querySelector('.spinner');
  const makerSpan = modal.querySelector('.ac-s-maker');
  const sumAmtSpan = modal.querySelector('.ac-s-amt');
  const sumTitleSpan = modal.querySelector('.ac-s-title');
  const focusableSelectors = 'button, [href], [tabindex]:not([tabindex="-1"])';
  let lastFocusedTrigger = null;

    const openModal = (bidRow, triggerBtn) => {
      activeBidRow = bidRow;
      activeBidId = bidRow.getAttribute('data-bid-id');
      const btn = triggerBtn;
      lastFocusedTrigger = btn;
      const amt = btn?.getAttribute('data-amount');
      const currency = btn?.getAttribute('data-currency');
      const maker = btn?.getAttribute('data-maker');
      const listingTitle = bidRow.closest('.bid-item-card')?.querySelector('.wi-title')?.textContent || '';
      amtSpan.textContent = amt && currency ? `${amt} ${currency}` : (bidRow.querySelector('.amt')?.textContent || 'this amount');
      makerSpan.textContent = maker || 'Unknown';
      sumAmtSpan.textContent = amtSpan.textContent;
      sumTitleSpan.textContent = listingTitle;
      errorMsg.hidden = true; errorMsg.textContent='';
      modal.hidden = false; modal.setAttribute('aria-hidden','false');
      confirmBtn.disabled=false; confirmBtn.querySelector('.btn-label').textContent='Accept Bid';
      spinner.hidden = true;
      // focus
      setTimeout(()=>confirmBtn.focus(), 30);
      document.body.style.overflow='hidden';
    };
    const closeModal = () => {
      modal.hidden = true; modal.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
      activeBidRow = null; activeBidId = null;
      if(lastFocusedTrigger) { lastFocusedTrigger.focus(); }
    };

    // Basic focus trap
    document.addEventListener('keydown', e => {
      if(e.key==='Tab' && !modal.hidden){
        const focusable = Array.from(modal.querySelectorAll(focusableSelectors)).filter(el=>!el.disabled && el.offsetParent!==null);
        if(!focusable.length) return;
        const first = focusable[0]; const last = focusable[focusable.length-1];
        if(e.shiftKey && document.activeElement===first){ e.preventDefault(); last.focus(); }
        else if(!e.shiftKey && document.activeElement===last){ e.preventDefault(); first.focus(); }
      }
    });

    document.addEventListener('keydown', e => { if(e.key==='Escape' && !modal.hidden) closeModal(); });
    modal.addEventListener('click', e => { if(e.target===modal) closeModal(); });

    document.addEventListener('click', async e => {
    // Expand toggle
    const expandBtn = e.target.closest('[data-expand-trigger]');
    if(expandBtn){
      const wrap = expandBtn.closest('[data-expand]');
      const list = wrap.querySelector('.extra');
      if(list.hidden){ list.hidden=false; expandBtn.textContent='Show less'; }
      else { list.hidden=true; expandBtn.textContent='+'+list.children.length+' more'; }
      return;
    }
      // Open modal accept flow
      const acceptBtn = e.target.closest('[data-accept-bid]');
      if(acceptBtn){
        const bidRow = acceptBtn.closest('.bid-row');
        openModal(bidRow, acceptBtn);
        return;
      }
      // Close controls
      if(e.target.matches('[data-ac-close]')) { closeModal(); return; }
      // Confirm in modal
      if(e.target.matches('[data-ac-confirm-modal]')){
        if(!activeBidRow || !activeBidId) return;
        confirmBtn.disabled=true; confirmBtn.querySelector('.btn-label').textContent='Accepting...'; spinner.hidden=false;
        errorMsg.hidden=true; errorMsg.textContent='';
        try {
          const res = await fetch(`/bids/${activeBidId}/status`, {
            method:'PATCH',
            headers:{
              'Accept':'application/json',
              'Content-Type':'application/json',
              'X-Requested-With':'XMLHttpRequest',
              'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: 'accepted' }),
            credentials:'include'
          });
          if(!res.ok){
            const txt = await res.text();
            throw new Error(txt || 'Failed');
          }
          const bidRow = activeBidRow;
          const card = bidRow.closest('.bid-item-card');
          // Update accepted bid pill
          bidRow.classList.add('accepted-highlight');
          const pill = bidRow.querySelector('.pill');
          pill.textContent='ACCEPTED'; pill.className='pill p-accepted';
          // Remove accept buttons only inside this card
          card.querySelectorAll('[data-accept-bid]').forEach(btn => btn.closest('.row-actions')?.remove());
          // Reject only other bids belonging to the same waste item
          card.querySelectorAll('.bid-row').forEach(r => {
            if(r!==bidRow){
              const sp = r.querySelector('.pill');
              if(sp && /PENDING/i.test(sp.textContent)) { sp.textContent='REJECTED'; sp.className='pill p-rejected'; }
            }
          });
          // Add accepted badge to this card if missing
          const amountTxt = bidRow.querySelector('.amt')?.textContent || '';
          if(card && !card.querySelector('.badge.accepted')){
            const media = card.querySelector('.card-media');
            const badge = document.createElement('span');
            badge.className='badge accepted';
            badge.textContent='Accepted '+amountTxt;
            media.appendChild(badge);
          }
          spinner.hidden=true;
          closeModal();
        } catch(err){
          console.error('[DashboardBids] Accept bid failed', err);
          confirmBtn.disabled=false; confirmBtn.querySelector('.btn-label').textContent='Accept Bid'; spinner.hidden=true;
          errorMsg.textContent='Failed to accept bid. Please retry.'; errorMsg.hidden=false;
        }
        return;
      }
    });
  })();
</script>
@endpush
