(function(){
  console.debug('[admin-listings] script loaded');
  const state = {
    page: 1,
    search: '',
    condition: '',
    sort: 'newest',
    pagination: null,
    currentId: null,
    keepImages: [],
    removeImages: [],
    newImages: [],
  };

  const tbody = document.getElementById('al-tbody');
  if(!tbody) return; // Not on listings page
  console.debug('[admin-listings] tbody found, attaching handlers');

  const searchInput = document.getElementById('al-search');
  const searchClearBtn = document.getElementById('al-search-clear');
  const condSelect = document.getElementById('al-condition');
  const sortSelect = document.getElementById('al-sort');
  const metaEl = document.getElementById('al-meta');
  const prevBtn = document.getElementById('al-prev');
  const nextBtn = document.getElementById('al-next');
  const pageInfo = document.getElementById('al-pageinfo');

  // Modals (generator style structure)
  const overlay = document.getElementById('al-modal-overlay');
  const viewModal = document.getElementById('al-view-modal');
  const viewLoading = document.getElementById('al-view-loading');
  const viewError = document.getElementById('al-view-error');
  const viewContent = document.getElementById('al-view-content');
  const viewSummary = document.getElementById('al-view-summary');
  const viewImages = document.getElementById('al-view-images');
  const viewNotes = document.getElementById('al-view-notes');
  const viewHeaderTitle = document.getElementById('al-vh-title');
  const viewMeta = document.getElementById('al-vh-meta');
  const viewImgCount = document.getElementById('al-view-img-count');
  const viewOpenLightboxBtn = document.getElementById('al-view-open-lightbox');
  // Lightbox elements
  const photosModal = document.getElementById('al-photos-modal');
  const photosLoader = document.getElementById('al-photos-loader');
  const photosError = document.getElementById('al-photos-error');
  const photosMainWrap = document.getElementById('al-photos-main-wrap');
  const photosMainImg = document.getElementById('al-photos-main-image');
  const photosCaption = document.getElementById('al-photos-caption');
  const photosThumbs = document.getElementById('al-photos-thumbs');
  let lightboxImages = [];
  let lightboxIndex = 0;
  const openEditFromView = document.getElementById('al-open-edit');
  const editModal = document.getElementById('al-edit-modal');
  const editForm = document.getElementById('al-edit-form');
  const deleteModal = document.getElementById('al-delete-modal');
  const deleteText = document.getElementById('al-delete-text');
  const deleteConfirm = document.getElementById('al-delete-confirm');

  function iconForCondition(cond){
    const c = (cond||'').toLowerCase();
    if(c==='good') return 'fa-check';
    if(c==='fixable') return 'fa-wrench';
    if(c==='scrap') return 'fa-recycle';
    return 'fa-circle';
  }

  // (Portal no longer required: modals already inside overlay like generator page)

  // Edit fields
  const ef = {
    title: document.getElementById('al-edit-title'),
    condition: document.getElementById('al-edit-condition'),
    weight: document.getElementById('al-edit-weight'),
    lat: document.getElementById('al-edit-lat'),
    lng: document.getElementById('al-edit-lng'),
    notes: document.getElementById('al-edit-notes'),
    existingWrap: document.getElementById('al-edit-existing'),
    newInput: document.getElementById('al-edit-new'),
    newPreviews: document.getElementById('al-edit-new-previews'),
    keepField: document.getElementById('al-edit-keep'),
    removeField: document.getElementById('al-edit-remove')
  };

  function anyOpen(){ return overlay ? overlay.querySelector('.modal:not(.hidden)') : null; }
  function showOverlay(){ if(overlay){ overlay.setAttribute('aria-hidden','false'); overlay.classList.add('active'); } }
  function hideOverlay(){ if(overlay){ overlay.setAttribute('aria-hidden','true'); overlay.classList.remove('active'); } }
  function openModal(m){ if(!m) return; showOverlay(); m.classList.remove('hidden'); document.body.classList.add('body-modal-open'); console.debug('[admin-listings] open modal', m.id); }
  function closeModal(m){ if(!m) return; m.classList.add('hidden'); if(!anyOpen()){ hideOverlay(); document.body.classList.remove('body-modal-open'); } }
  (overlay||document).querySelectorAll('[data-close]').forEach(btn=>btn.addEventListener('click', ()=>{ closeModal(btn.closest('.modal')); }));
  window.addEventListener('keydown', e=>{ if(e.key==='Escape'){ (overlay||document).querySelectorAll('.modal').forEach(m=>closeModal(m)); }});
  // Optional: click on backdrop closes if target is overlay itself
  overlay?.addEventListener('mousedown', e=>{ if(e.target === overlay){ (overlay.querySelectorAll('.modal:not(.hidden)')||[]).forEach(m=>closeModal(m)); } });

  function fetchList(){
    const params = new URLSearchParams({
      search: state.search||'',
      condition: state.condition||'',
      sort: state.sort||'newest',
      page: state.page
    });
    fetch(`/admin/listings?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
      .then(r=>r.json())
      .then(json=>{
        const items = json.data.items;
        tbody.innerHTML = '';
        items.forEach(i=>{
          const tr = document.createElement('tr');
          tr.dataset.id = i.id;
          tr.innerHTML = `<td>${i.id}</td><td>${escapeHtml(i.title)}</td><td><span class="badge cond-${i.condition}">${i.condition}</span></td><td>${escapeHtml(i.generator||'—')}</td><td>${i.images_count}</td><td>${formatDate(i.created_at)}</td><td class="actions">`+
            `<div class="action-group">`+
              `<button class="icon-btn view is-blue" data-action="view" data-view data-tooltip="View" aria-label="View listing"><i class="fa-solid fa-eye"></i><span class="sr-only">View</span></button>`+
              `<button class="icon-btn edit is-green" data-action="edit" data-edit data-tooltip="Edit" aria-label="Edit listing"><i class="fa-solid fa-pen"></i><span class="sr-only">Edit</span></button>`+
              `<button class="icon-btn delete is-red" data-action="delete" data-delete data-tooltip="Delete" aria-label="Delete listing"><i class="fa-solid fa-trash"></i><span class="sr-only">Delete</span></button>`+
            `</div>`+
          `</td>`;
          tbody.appendChild(tr);
        });
        state.pagination = json.data.pagination;
        updatePagination();
        metaEl.textContent = `${state.pagination.total} total`;
      }).catch(err=>console.error(err));
  }

  function updatePagination(){
    if(!state.pagination) return;
    prevBtn.disabled = !state.pagination.prev_page_url;
    nextBtn.disabled = !state.pagination.next_page_url;
    pageInfo.textContent = `Page ${state.pagination.current_page} of ${state.pagination.last_page}`;
  }

  function escapeHtml(str){return (str||'').replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[s]));}
  function formatDate(iso){ if(!iso) return '—'; const d = new Date(iso); return d.toLocaleDateString()+ ' '+d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); }

  function updateSearchState(){
    const wrapper = searchInput?.closest('.tb-search');
    if(!wrapper) return;
    if(searchInput.value.trim().length){ wrapper.classList.add('has-value'); }
    else { wrapper.classList.remove('has-value'); }
  }
  let searchTimer; if(searchInput){
    searchInput.addEventListener('input', ()=>{
      updateSearchState();
      clearTimeout(searchTimer);
      searchTimer = setTimeout(()=>{ state.search = searchInput.value.trim(); state.page = 1; fetchList(); }, 280);
    });
    updateSearchState();
  }
  searchClearBtn?.addEventListener('click', ()=>{
    if(!searchInput) return;
    searchInput.value='';
    updateSearchState();
    state.search='';
    state.page=1;
    fetchList();
    searchInput.focus();
  });
  condSelect?.addEventListener('change', ()=>{ state.condition = condSelect.value; state.page=1; fetchList(); });
  sortSelect?.addEventListener('change', ()=>{ state.sort = sortSelect.value; state.page=1; fetchList(); });
  prevBtn?.addEventListener('click', ()=>{ if(state.pagination?.prev_page_url){ state.page = Math.max(1, state.pagination.current_page - 1); fetchList(); }});
  nextBtn?.addEventListener('click', ()=>{ if(state.pagination?.next_page_url){ state.page = state.pagination.current_page + 1; fetchList(); }});

  // Delegated actions
  tbody.addEventListener('click', e=>{
    const btn = e.target.closest('button.icon-btn'); if(!btn) return;
    const tr = btn.closest('tr'); const id = tr?.dataset.id; if(!id) return;
    const action = btn.getAttribute('data-action') || (btn.hasAttribute('data-view')?'view': btn.hasAttribute('data-edit')?'edit': btn.hasAttribute('data-delete')?'delete': null);
    if(!action) return;
    if(action==='view'){ console.debug('[admin-listings] view button clicked for id', id); loadView(id); }
    else if(action==='edit'){ loadEdit(id); }
    else if(action==='delete'){ confirmDelete(id, tr); }
  });

  function resetViewStates(){
    if(viewLoading) viewLoading.classList.add('hidden');
    viewError?.classList.add('hidden');
    viewContent?.classList.add('hidden');
  }
  function loadView(id){
    resetViewStates();
    if(viewLoading) viewLoading.classList.remove('hidden');
    openModal(viewModal);
    if(!viewModal){ console.warn('[admin-listings] viewModal element missing in DOM'); }
    if(!viewLoading){ console.warn('[admin-listings] viewLoading element missing'); }
    fetch(`/admin/listings/${id}`, { headers: {'Accept':'application/json'} })
      .then(r=>{ if(!r.ok) throw new Error('Load failed'); return r.json(); })
      .then(json=>{
        const d = json.data; state.currentId = d.id;
        console.debug('[admin-listings] loaded listing data', d.id);
        if(viewHeaderTitle) viewHeaderTitle.textContent = d.title || '—';
        if(viewMeta){
          viewMeta.innerHTML = '';
          const condSpan = document.createElement('span'); condSpan.className = 'al-pill cond-'+(d.condition||'none'); condSpan.innerHTML = `<i class="fa-solid ${iconForCondition(d.condition)}"></i>${(d.condition||'—').toUpperCase()}`; viewMeta.appendChild(condSpan);
          const weightSpan = document.createElement('span'); weightSpan.className='al-pill'; weightSpan.innerHTML = `<i class="fa-solid fa-weight-hanging"></i>${(d.estimated_weight ?? '—')} kg`; viewMeta.appendChild(weightSpan);
          const idSpan = document.createElement('span'); idSpan.className='al-pill'; idSpan.innerHTML = `<i class="fa-solid fa-hashtag"></i>${d.id}`; viewMeta.appendChild(idSpan);
          const locSpan = document.createElement('span'); locSpan.className='al-pill'; locSpan.innerHTML = `<i class="fa-solid fa-location-dot"></i>${d.location? ((d.location.lat ?? '—')+','+(d.location.lng ?? '—')) : 'No location'}`; viewMeta.appendChild(locSpan);
        }
        if(viewSummary){
          viewSummary.innerHTML = '';
          const entries = [
            ['Created', formatDate(d.created_at)],
            ['Updated', formatDate(d.updated_at)],
            ['Materials', d.materials_count],
            ['Location', d.location? ( (d.location.lat ?? '—')+','+(d.location.lng ?? '—') ) : 'No location']
          ];
          entries.forEach(([k,v])=>{
            const dt=document.createElement('dt'); dt.textContent=k; const dd=document.createElement('dd'); dd.textContent=v; viewSummary.appendChild(dt); viewSummary.appendChild(dd);
          });
        }
        if(viewNotes){ viewNotes.textContent = d.notes?.trim() ? d.notes : '—'; }
        viewImages.innerHTML='';
        d.images.forEach((img, idx)=>{
          const wrap = document.createElement('div');
          wrap.className='img-box';
          wrap.innerHTML = `<img src="${img.url}" alt="Image ${idx+1}" data-index="${idx}" class="al-thumb" loading="lazy" />`;
          viewImages.appendChild(wrap);
        });
        if(viewImgCount) viewImgCount.textContent = d.images.length;
        if(viewOpenLightboxBtn){ viewOpenLightboxBtn.style.display = d.images.length ? '' : 'none'; }
        if(viewLoading) viewLoading.classList.add('hidden');
        viewContent?.classList.remove('hidden');
      })
      .catch(()=>{
        if(viewLoading) viewLoading.classList.add('hidden');
        viewError?.classList.remove('hidden');
        console.warn('[admin-listings] failed to load listing id', id);
      });
  }
  // Lightbox
  function openPhotosLightbox(id){
    if(!photosModal) return;
    photosError?.classList.add('hidden');
    photosMainWrap?.classList.add('hidden');
    photosLoader?.classList.remove('hidden');
    photosThumbs.innerHTML='';
    photosCaption.textContent='';
    lightboxImages=[]; lightboxIndex=0;
    openModal(photosModal);
    fetch(`/admin/listings/${id}`, { headers:{'Accept':'application/json'} })
      .then(r=>{ if(!r.ok) throw new Error('Fail'); return r.json(); })
      .then(json=>{
        const imgs = (json.data.images||[]).sort((a,b)=>(a.order??0)-(b.order??0));
        lightboxImages = imgs.map(i=>i.url);
        if(!lightboxImages.length){ throw new Error('empty'); }
        lightboxImages.forEach((src,i)=>{
          const t=document.createElement('img'); t.src=src; t.alt='Photo '+(i+1); t.dataset.index=i; photosThumbs.appendChild(t);
        });
        photosThumbs.addEventListener('click', onThumbClick, { once:true });
        setLightboxIndex(0);
        photosLoader?.classList.add('hidden');
        photosMainWrap?.classList.remove('hidden');
      })
      .catch(()=>{
        photosLoader?.classList.add('hidden');
        photosError?.classList.remove('hidden');
      });
  }
  function onThumbClick(e){
    const img=e.target.closest('img[data-index]'); if(!img) return; const i=parseInt(img.dataset.index,10); if(!isNaN(i)) setLightboxIndex(i); photosThumbs.addEventListener('click', onThumbClick);
  }
  function setLightboxIndex(i){
    if(!lightboxImages.length) return; lightboxIndex=(i+lightboxImages.length)%lightboxImages.length;
    const src=lightboxImages[lightboxIndex];
    photosMainImg.src = src + (src.includes('?')?'&':'?')+'v='+Date.now();
    photosMainImg.classList.remove('hidden');
    photosThumbs.querySelectorAll('img').forEach(t=>t.classList.toggle('active', parseInt(t.dataset.index,10)===lightboxIndex));
    photosCaption.textContent = `Image ${lightboxIndex+1} of ${lightboxImages.length}`;
    const prevBtn = photosMainWrap.querySelector('.lb-nav.prev');
    const nextBtn = photosMainWrap.querySelector('.lb-nav.next');
    if(prevBtn) prevBtn.style.display = lightboxImages.length>1? '' : 'none';
    if(nextBtn) nextBtn.style.display = lightboxImages.length>1? '' : 'none';
  }
  function navigateLightbox(delta){ if(!lightboxImages.length) return; setLightboxIndex(lightboxIndex+delta); }
  photosMainWrap?.querySelector('.lb-nav.prev')?.addEventListener('click', ()=>navigateLightbox(-1));
  photosMainWrap?.querySelector('.lb-nav.next')?.addEventListener('click', ()=>navigateLightbox(1));
  viewOpenLightboxBtn?.addEventListener('click', ()=>{ if(state.currentId) openPhotosLightbox(state.currentId); });
  viewImages?.addEventListener('click', e=>{ const t=e.target.closest('img.al-thumb'); if(t && state.currentId){ openPhotosLightbox(state.currentId); }});
  window.addEventListener('keydown', e=>{ if(photosModal && !photosModal.classList.contains('hidden')){ if(e.key==='ArrowRight') navigateLightbox(1); if(e.key==='ArrowLeft') navigateLightbox(-1); }});
  openEditFromView?.addEventListener('click', ()=>{ if(state.currentId) { closeModal(viewModal); loadEdit(state.currentId); } });

  function loadEdit(id){
    fetch(`/admin/listings/${id}`, { headers: {'Accept':'application/json'} })
      .then(r=>r.json())
      .then(json=>{
        const d = json.data; state.currentId = d.id;
        ef.title.value = d.title||'';
        ef.condition.value = d.condition||'good';
        ef.weight.value = d.estimated_weight||'';
        ef.lat.value = d.location?.lat ?? '';
        ef.lng.value = d.location?.lng ?? '';
        ef.notes.value = d.notes||'';
        state.keepImages = d.images.map(i=>i.id);
        state.removeImages = [];
        renderExistingImages(d.images);
        ef.newPreviews.innerHTML='';
        ef.newInput.value='';
        openModal(editModal);
      });
  }

  function renderExistingImages(imgs){
    ef.existingWrap.innerHTML = '';
    imgs.sort((a,b)=>a.order-b.order).forEach(img=>{
      const box = document.createElement('div');
      box.className = 'img-chip';
      box.dataset.id = img.id;
      box.innerHTML = `<img src="${img.url}" alt="${img.id}" />`+
        `<button type="button" class="rm" aria-label="Remove" title="Remove">&times;</button>`;
      box.querySelector('.rm').addEventListener('click', ()=>{
        const id = img.id;
        if(!state.removeImages.includes(id)) state.removeImages.push(id);
        state.keepImages = state.keepImages.filter(k=>k!==id);
        box.remove();
      });
      ef.existingWrap.appendChild(box);
    });
  }

  ef.newInput?.addEventListener('change', ()=>{
    ef.newPreviews.innerHTML='';
    Array.from(ef.newInput.files||[]).forEach(file=>{
      const url = URL.createObjectURL(file);
      const chip = document.createElement('div');
      chip.className = 'img-chip new';
      chip.innerHTML = `<img src="${url}" alt="new" />`;
      ef.newPreviews.appendChild(chip);
    });
  });

  editForm?.addEventListener('submit', async e=>{
    e.preventDefault(); if(!state.currentId) return;
    // sync hidden fields
    ef.keepField.value = state.keepImages.join(',');
    ef.removeField.value = state.removeImages.join(',');
    const formData = new FormData(editForm);
    // true PUT for JSON fields + files is tricky; easier: spoof method via _method field
    if(!formData.has('_method')) formData.append('_method','PUT');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
      const resp = await fetch(`/admin/listings/${state.currentId}`, {
        method: 'POST',
        headers: { 'Accept':'application/json','X-CSRF-TOKEN': csrf },
        body: formData
      });
      if(!resp.ok){
        console.warn('[admin-listings] update failed status', resp.status);
        try { const err = await resp.json(); console.warn(err); } catch {}
        alert('Update failed.');
        return;
      }
      await resp.json().catch(()=>null);
      closeModal(editModal);
      fetchList();
    } catch(err){
      console.error('[admin-listings] update error', err);
      alert('Network error updating listing');
    }
  });

  function confirmDelete(id, tr){
    state.currentId = id;
    deleteText.textContent = `Delete listing #${id}? This can be restored later (soft delete).`;
    openModal(deleteModal);
    deleteConfirm.onclick = () => doDelete(id, tr);
  }

  function doDelete(id, tr){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const fd = new FormData(); fd.append('_method','DELETE');
    fetch(`/admin/listings/${id}`, { method: 'POST', headers: { 'Accept':'application/json','X-CSRF-TOKEN': csrf }, body: fd })
      .then(async r=>{
        if(!r.ok){ console.warn('[admin-listings] delete failed', r.status); alert('Delete failed'); return; }
        try { await r.json(); } catch {}
        tr?.remove();
        closeModal(deleteModal);
        fetchList();
      }).catch(err=>{ console.error('[admin-listings] delete error', err); alert('Network error deleting'); });
  }

  // Initial fetch after DOM ready (replace server-provided table soon)
  fetchList();
})();
