import '../css/materials.css';

// Marketplace photo lightbox (adapted from generator waste items lightbox)
document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('marketplaceModalOverlay');
  const modal = document.getElementById('marketplacePhotosModal');
  // Bid modal elements
  const bidOverlay = document.getElementById('bidModalOverlay');
  const bidModal = document.getElementById('marketplaceBidModal');
  const bidForm = document.getElementById('bidForm');
  const bidWasteItemId = document.getElementById('bidWasteItemId');
  const bidSubmitBtn = document.getElementById('bidSubmitBtn');
  const bidFeedback = document.getElementById('bidFeedback');
  const bidExistingWrap = document.getElementById('bidExistingWrap');
  const bidExistingList = document.getElementById('bidExistingList');
  if(!modal){ console.warn('[Marketplace] Photos modal not found'); return; }
  console.log('[Marketplace] Lightbox script init');

  const loader   = document.getElementById('mpPhotosLoader');
  const errorBox = document.getElementById('mpPhotosError');
  const mainWrap = document.getElementById('mpPhotosMainWrap');
  const mainImg  = document.getElementById('mpPhotosMainImage');
  const thumbs   = document.getElementById('mpPhotosThumbs');
  const caption  = document.getElementById('mpPhotosCaption');
  const prevBtn  = modal.querySelector('.lb-nav.prev');
  const nextBtn  = modal.querySelector('.lb-nav.next');
  const closeBtns = modal.querySelectorAll('[data-close], .modal-close');

  let images = [];
  let index = 0;

  function openModal(){
    overlay?.setAttribute('aria-hidden','false');
    overlay?.classList.add('active');
    modal.classList.remove('hidden');
  }
  function closeModal(){
    modal.classList.add('hidden');
    overlay?.classList.remove('active');
    overlay?.setAttribute('aria-hidden','true');
  }

  function setLoading(){
    loader?.classList.remove('hidden');
    loader?.removeAttribute('aria-hidden');
    errorBox?.classList.add('hidden');
    mainWrap?.classList.add('hidden');
    thumbs.innerHTML='';
    caption.textContent='';
  }
  function setError(){
    loader?.classList.add('hidden');
    errorBox?.classList.remove('hidden');
    mainWrap?.classList.add('hidden');
  }
  function setReady(){
    loader?.classList.add('hidden');
    errorBox?.classList.add('hidden');
    mainWrap?.classList.remove('hidden');
  }

  function renderThumbs(){
    thumbs.innerHTML='';
    images.forEach((img,i)=>{
      const seg=document.createElement('button');
      seg.type='button';
      seg.className='lb-thumb'+(i===index?' active':'');
      seg.dataset.idx=i;
      seg.addEventListener('click',()=>{ index=i; showCurrent(); });
      thumbs.appendChild(seg);
    });
  }
  function showCurrent(){
    if(!images.length){
      mainImg.removeAttribute('src');
      caption.textContent='No photos';
      return;
    }
    const cur=images[index];
    mainImg.src=cur.url;
    caption.textContent=`${cur.caption||''} (${index+1}/${images.length})`;
    renderThumbs();
  }
  function next(){ if(images.length){ index=(index+1)%images.length; showCurrent(); } }
  function prev(){ if(images.length){ index=(index-1+images.length)%images.length; showCurrent(); } }

  async function openPhotosLightbox(id){
    console.log('[Marketplace] Opening photos for item', id);
    setLoading();
    openModal();
    try {
      const res = await fetch(`/marketplace/${id}`, { headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} });
      if(!res.ok) throw new Error('Fetch failed');
      const json = await res.json();
      const data = json.data || {};
      images = (data.images||[]).map(img=>({url:img.url, caption:data.title}));
      if(!images.length && data.primary_image_url){
        images=[{url:data.primary_image_url, caption:data.title}];
      }
      index=0;
      setReady();
      showCurrent();
      console.log('[Marketplace] Loaded images count', images.length);
    } catch(err){
      console.error('[Marketplace] Lightbox load error', err);
      setError();
    }
  }

  // Delegated click for photos buttons
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-photos');
    if(!btn) return;
    const id = btn.getAttribute('data-id');
    if(!id) return;
    openPhotosLightbox(id);
  });

  // Bid modal open
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-bid');
    if(!btn) return;
    const id = btn.getAttribute('data-id');
    if(!id) return;
    openBidModal(id);
  });

  function openBidModal(id){
    if(!bidModal) return;
    clearBidForm();
    bidWasteItemId.value = id;
    bidOverlay?.setAttribute('aria-hidden','false');
    bidOverlay?.classList.add('active');
    bidModal.classList.remove('hidden');
    loadExistingBids(id);
  }
  function closeBidModal(){
    bidModal?.classList.add('hidden');
    bidOverlay?.classList.remove('active');
    bidOverlay?.setAttribute('aria-hidden','true');
  }
  bidOverlay?.addEventListener('click', e => { if(e.target===bidOverlay) closeBidModal(); });
  bidModal?.querySelectorAll('[data-close], .modal-close').forEach(b=>b.addEventListener('click', closeBidModal));
  document.addEventListener('keydown', e=>{ if(e.key==='Escape' && bidOverlay?.classList.contains('active')) closeBidModal(); });

  function clearBidForm(){
    if(!bidForm) return;
    const currentId = bidWasteItemId?.value;
    bidForm.reset();
    if(currentId) bidWasteItemId.value = currentId; // preserve selected waste item id
    [...bidForm.querySelectorAll('.error')].forEach(el=>{el.textContent='';});
    bidFeedback.hidden = true;
    bidFeedback.textContent='';
    toggleBidSubmitting(false);
  }
  function toggleBidSubmitting(submitting){
    if(!bidSubmitBtn) return;
    const def = bidSubmitBtn.querySelector('.btn-label-default');
    const load = bidSubmitBtn.querySelector('.btn-label-loading');
    if(submitting){
      bidSubmitBtn.disabled = true;
      def.hidden = true; load.hidden = false;
    } else {
      bidSubmitBtn.disabled = false;
      def.hidden = false; load.hidden = true;
    }
  }
  async function loadExistingBids(id){
    if(!bidExistingList) return;
    bidExistingList.innerHTML = '<div class="loading-sm"><i class="fa-solid fa-circle-notch fa-spin"></i> Loading bids...</div>';
    try {
      const res = await fetch(`/waste-items/${id}/bids`, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'include' });
      if(!res.ok) throw new Error('Failed');
      const json = await res.json();
      const data = json.data || json; // paginate vs flat
      renderBidList(data);
    } catch(_e){
      bidExistingList.innerHTML = '<div class="error-text">Failed to load bids.</div>';
    }
  }
  function renderBidList(data){
    if(!Array.isArray(data) || !data.length){
      bidExistingWrap.hidden = false;
      bidExistingList.innerHTML = '<div class="empty-bids">No bids yet. Be the first!</div>';
      return;
    }
    bidExistingWrap.hidden = false;
    bidExistingList.innerHTML = data.map(b => bidItemHtml(b)).join('');
  }
  function bidItemHtml(b){
    const status = b.status || 'pending';
    return `<div class="bid-row bid-status-${status}">
      <div class="bid-main">
        <span class="bid-amount">${Number(b.amount).toFixed(2)} ${b.currency}</span>
        <span class="bid-maker">by ${b.maker?.name || 'Unknown'}</span>
      </div>
      <div class="bid-meta">
        <span class="bid-status-badge">${status}</span>
      </div>
    </div>`;
  }
  bidForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const id = bidWasteItemId.value;
    if(!id) return;
    toggleBidSubmitting(true);
    bidFeedback.hidden = true; bidFeedback.textContent='';
    [...bidForm.querySelectorAll('.error')].forEach(el=>el.textContent='');
    const formData = new FormData(bidForm);
    try {
      const res = await fetch(`/waste-items/${id}/bids`, {
        method:'POST',
        headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').content },
        body: formData,
        credentials:'include'
      });
      if(res.status===422){
        const err = await res.json();
        const errs = err.errors || {};
        Object.keys(errs).forEach(f=>{
          const el = bidForm.querySelector(`[data-error-for="${f}"]`);
          if(el) el.textContent = errs[f][0];
        });
        throw new Error('Validation failed');
      }
      if(res.status===401){
        bidFeedback.hidden = false;
        bidFeedback.className = 'form-row bid-feedback error';
        bidFeedback.textContent = 'You must be signed in to submit a bid.';
        throw new Error('Unauthorized');
      }
      if(res.status===403){
        // Likely bidding on own waste item or policy rejection
        let msg = 'You are not allowed to bid on this item.';
        try { const j = await res.json(); if(j?.message) msg = j.message; } catch(_) {}
        bidFeedback.hidden = false;
        bidFeedback.className = 'form-row bid-feedback error';
        bidFeedback.textContent = msg;
        throw new Error('Forbidden');
      }
      if(!res.ok) throw new Error('Request failed');
      const _created = await res.json();
      // Refresh list
      loadExistingBids(id);
      bidFeedback.hidden = false;
      bidFeedback.className = 'form-row bid-feedback success';
      bidFeedback.textContent = 'Bid submitted successfully!';
      // keep amount cleared but preserve waste item id for quick second bid
      const currentId = bidWasteItemId.value;
      clearBidForm();
      if(currentId) bidWasteItemId.value = currentId;
    } catch(err){
      console.error('[Marketplace] Bid submit error', err);
      bidFeedback.hidden = false;
      bidFeedback.className = 'form-row bid-feedback error';
      if(!bidFeedback.textContent || bidFeedback.textContent === 'Failed to submit bid.'){
        bidFeedback.textContent = 'Failed to submit bid.';
      }
    } finally {
      toggleBidSubmitting(false);
    }
  });

  // Modal interactions
  overlay?.addEventListener('click', e => { if(e.target===overlay) closeModal(); });
  closeBtns.forEach(b=>b.addEventListener('click', closeModal));
  nextBtn?.addEventListener('click', next);
  prevBtn?.addEventListener('click', prev);
  document.addEventListener('keydown', e => {
    if(modal.classList.contains('hidden')) return;
    if(e.key==='Escape') closeModal();
    if(e.key==='ArrowRight') next();
    if(e.key==='ArrowLeft') prev();
  });
});
