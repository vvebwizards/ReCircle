import '../css/materials.css';

// Marketplace photo lightbox (adapted from generator waste items lightbox)
document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('marketplaceModalOverlay');
  const modal = document.getElementById('marketplacePhotosModal');
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
