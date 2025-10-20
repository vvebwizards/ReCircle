@extends('layouts.app')

@push('head')
@vite(['resources/css/maker-bids.css'])
@endpush

@section('title')
My Bids - ReCircle
@endsection

@section('content')
<main class="maker-bids">
  <div class="container">
    <header class="mb-header">
      <h1><i class="fa-solid fa-hand-holding-dollar"></i> My Bids</h1>
      <p class="sub">Track bids you've placed across listings. Filter by status, listing, date, or amount.</p>
    </header>

    <form method="GET" class="filters" action="{{ route('maker.bids') }}" id="makerBidsFilters">
      <div class="f-row">
        <div class="f-field">
          <label>Status</label>
          <select name="status">
            <option value="">All</option>
            @foreach(['pending','accepted','rejected','withdrawn'] as $st)
              <option value="{{ $st }}" @selected($filters['status']===$st)>{{ ucfirst($st) }}</option>
            @endforeach
          </select>
        </div>
        <div class="f-field">
          <label>Listing Title</label>
          <input type="text" name="waste" value="{{ $filters['waste'] }}" placeholder="Search title..." />
        </div>
        <div class="f-field">
          <label>From</label>
          <input type="date" name="from" value="{{ $filters['from'] }}" />
        </div>
        <div class="f-field">
          <label>To</label>
          <input type="date" name="to" value="{{ $filters['to'] }}" />
        </div>
        <div class="f-field">
          <label>Min Amount</label>
          <input type="number" step="0.01" name="min_amount" value="{{ $filters['min_amount'] }}" />
        </div>
        <div class="f-field">
          <label>Max Amount</label>
          <input type="number" step="0.01" name="max_amount" value="{{ $filters['max_amount'] }}" />
        </div>
        <div class="f-actions">
          <a href="{{ route('maker.bids') }}" class="btn secondary sm btn-reset" data-reset>Reset</a>
        </div>
      </div>
      <div class="active-chips" id="activeFilterChips" aria-live="polite"></div>
    </form>

    @if($bids->isEmpty())
      <div class="empty-state">
        <p class="title">No bids found</p>
        <p class="hint">Place a bid from the marketplace to see it listed here.</p>
        <a href="{{ route('marketplace.index') }}" class="btn primary sm"><i class="fa-solid fa-store"></i> Browse Marketplace</a>
      </div>
    @else
      <div class="table-wrap">
        <table class="bids-table">
          <thead>
            <tr>
              <th>Listing</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Placed</th>
              <th>Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($bids as $bid)
              <tr class="st-{{ $bid->status }}" data-bid-row data-bid-id="{{ $bid->id }}">
                <td class="listing">
                  <div class="lst">
                    @php $photo = $bid->wasteItem->photos->first(); $img = $photo->image_url ?? $photo->image_path ?? asset('images/default-material.png'); @endphp
                    <img src="{{ $img }}" alt="{{ $bid->wasteItem->title }}" />
                    <span>{{ $bid->wasteItem->title }}</span>
                  </div>
                </td>
                <td>{{ number_format($bid->amount,2) }} {{ $bid->currency }}</td>
                <td><span class="pill p-{{ $bid->status }}">{{ strtoupper($bid->status) }}</span></td>
                <td>{{ $bid->created_at->diffForHumans() }}</td>
                <td>{{ $bid->updated_at->diffForHumans() }}</td>
                <td>
                  @if($bid->status==='pending')
                    <button type="button" class="btn-withdraw" data-withdraw data-bid-id="{{ $bid->id }}">Withdraw</button>
                  @else
                    <span class="muted">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="pagination">{{ $bids->links() }}</div>
    @endif
  </div>
</main>
@endsection
@push('scripts')
<script>
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-withdraw]');
  if(!btn) return;
});

// Withdraw Modal Logic
(function(){
  const modalHtml = `
    <div class="wb-overlay" id="withdrawModal" hidden aria-hidden="true" role="dialog" aria-modal="true">
      <div class="wb-modal" role="document">
        <button type="button" class="wb-close" data-wb-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        <div class="wb-head">
          <div class="wb-icon"><i class="fa-solid fa-arrow-rotate-left"></i></div>
          <div class="wb-texts">
            <h2>Withdraw bid?</h2>
            <p class="wb-body">You are about to withdraw your bid of <span class="wb-amt"></span> on <span class="wb-title"></span>. This cannot be undone and the generator will no longer see it as active.</p>
          </div>
        </div>
        <div class="wb-summary">
          <div class="row"><span class="lbl">Listing</span><span class="val wb-s-title"></span></div>
          <div class="row"><span class="lbl">Amount</span><span class="val wb-s-amt"></span></div>
          <div class="row"><span class="lbl">Status</span><span class="val wb-s-status">PENDING</span></div>
        </div>
        <p class="wb-warn"><i class="fa-solid fa-triangle-exclamation"></i> This action is final.</p>
        <p class="wb-error" hidden aria-live="assertive"></p>
        <div class="wb-actions">
          <button type="button" class="btn-wb-outline" data-wb-close>Cancel</button>
          <button type="button" class="btn-wb-primary" data-wb-confirm>
            <span class="spinner" hidden></span>
            <span class="lbl">Withdraw Bid</span>
          </button>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  const modal = document.getElementById('withdrawModal');
  const amtSpan = modal.querySelector('.wb-amt');
  const titleInline = modal.querySelector('.wb-title');
  const sTitle = modal.querySelector('.wb-s-title');
  const sAmt = modal.querySelector('.wb-s-amt');
  const sStatus = modal.querySelector('.wb-s-status');
  const confirmBtn = modal.querySelector('[data-wb-confirm]');
  const spinner = modal.querySelector('.spinner');
  const errorMsg = modal.querySelector('.wb-error');
  let activeBtn=null; let activeBidId=null; let activeRow=null; let lastFocus=null;
  function openModal(btn){
    activeBtn=btn; activeBidId=btn.getAttribute('data-bid-id'); activeRow=btn.closest('[data-bid-row]'); lastFocus=document.activeElement;
    const amountCell = activeRow.querySelector('td:nth-child(2)');
    const listingTitle = activeRow.querySelector('.lst span')?.textContent || '';
    const amtText = amountCell?.textContent.trim() || '';
    amtSpan.textContent=amtText; titleInline.textContent=listingTitle;
    sTitle.textContent=listingTitle; sAmt.textContent=amtText; sStatus.textContent='PENDING';
    errorMsg.hidden=true; errorMsg.textContent='';
    modal.hidden=false; modal.setAttribute('aria-hidden','false');
    
    // Safely set button text and state
    confirmBtn.disabled=false; 
    spinner.hidden=true;
    
    // Save original button HTML if needed
    confirmBtn._originalHTML = confirmBtn.innerHTML;
    
    // Check if .lbl exists before trying to set it
    const lblElement = confirmBtn.querySelector('.lbl');
    if (lblElement) {
      lblElement.textContent='Withdraw Bid';
    }
    
    setTimeout(()=>confirmBtn.focus(), 30); 
    document.body.style.overflow='hidden';
  }
  function closeModal(){ modal.hidden=true; modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; if(lastFocus) lastFocus.focus(); activeBtn=null; activeBidId=null; activeRow=null; }
  document.addEventListener('click', e => {
    const wBtn = e.target.closest('[data-withdraw]');
    if(wBtn){ e.preventDefault(); openModal(wBtn); }
    if(e.target.matches('[data-wb-close]') || e.target===modal){ closeModal(); }
  });
  document.addEventListener('keydown', e => { if(e.key==='Escape' && !modal.hidden) closeModal(); });
  confirmBtn.addEventListener('click', async () => {
    if(!activeBidId || !activeRow) return;
    
    // Safely update button text and state - check if .lbl exists first
    const lblElement = confirmBtn.querySelector('.lbl');
    confirmBtn.disabled = true;
    spinner.hidden = false;
    
    if (lblElement) {
      lblElement.textContent = 'Withdrawing...';
    } else {
      // Fallback if .lbl isn't found - save original button content
      confirmBtn._originalHTML = confirmBtn.innerHTML;
      confirmBtn.innerHTML = '<span class="spinner"></span> Withdrawing...';
    }
    
    errorMsg.hidden = true;
    errorMsg.textContent = '';
    
    try {
      const res = await fetch(`/bids/${activeBidId}/withdraw`, {
        method:'PATCH',
        headers:{
          'Accept':'application/json',
          'Content-Type':'application/json',
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').content
        },
        credentials:'include'
      });
      
      if(!res.ok){ throw new Error(await res.text() || 'Failed'); }
      
      // Update row
      const pill = activeRow.querySelector('.pill');
      if (pill) {
        pill.textContent = 'WITHDRAWN'; 
        pill.className = 'pill p-withdrawn';
      }
      
      // Replace the button with a dash
      const span = document.createElement('span');
      span.textContent = '—';
      activeBtn.replaceWith(span);
      
      closeModal();
    } catch(err){
      console.error('Withdraw failed', err);
      confirmBtn.disabled = false;
      spinner.hidden = true;
      
      // Safely restore button text
      const lblElement = confirmBtn.querySelector('.lbl');
      if (lblElement) {
        lblElement.textContent = 'Withdraw Bid';
      } else if (confirmBtn._originalHTML) {
        // Use saved original HTML if no .lbl
        confirmBtn.innerHTML = confirmBtn._originalHTML;
      } else {
        // Complete fallback
        confirmBtn.innerHTML = 'Withdraw Bid';
      }
      
      errorMsg.textContent = 'Failed to withdraw. Try again.';
      errorMsg.hidden = false;
    }
  });
})();

// Dynamic filter chips
const form = document.getElementById('makerBidsFilters');
const chipsWrap = document.getElementById('activeFilterChips');
function renderChips(){
  if(!form || !chipsWrap) return;
  chipsWrap.innerHTML='';
  const entries = {
    status: form.status.value && form.status.value !== '' ? ['Status', form.status.options[form.status.selectedIndex].text] : null,
    waste: form.waste.value ? ['Listing', form.waste.value] : null,
    from: form.from.value ? ['From', form.from.value] : null,
    to: form.to.value ? ['To', form.to.value] : null,
    min_amount: form.min_amount.value ? ['Min', form.min_amount.value] : null,
    max_amount: form.max_amount.value ? ['Max', form.max_amount.value] : null,
  };
  Object.entries(entries).forEach(([name,val])=>{
    if(!val) return;
    const chip=document.createElement('button');
    chip.type='button';
    chip.className='chip-filter';
    chip.setAttribute('data-chip', name);
    chip.innerHTML=`<span class="c-label">${val[0]}:</span> <span class="c-val">${val[1]}</span> <i class="fa-solid fa-xmark"></i>`;
    chipsWrap.appendChild(chip);
  });
  chipsWrap.classList.toggle('has-chips', chipsWrap.children.length>0);
}
if(form){
  let submitTimer=null;
  const triggerSubmit=()=>{ clearTimeout(submitTimer); submitTimer=setTimeout(()=>{ form.requestSubmit(); }, 450); };
  form.addEventListener('input', (e)=>{
    if(e.target.matches('input,select')) { renderChips(); triggerSubmit(); }
  });
  form.addEventListener('change', (e)=>{
    if(e.target.matches('select,input[type=date]')) { renderChips(); triggerSubmit(); }
  });
  document.addEventListener('click', e => {
    const chipBtn = e.target.closest('.chip-filter');
    if(chipBtn){
      const field = chipBtn.getAttribute('data-chip');
      if(form[field]){ form[field].value=''; }
      renderChips(); triggerSubmit();
    }
  });
  renderChips();
}
</script>
@endpush
