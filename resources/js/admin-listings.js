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
  const viewSummary = document.getElementById('al-view-summary');
  const viewImages = document.getElementById('al-view-images');
  const viewNotes = document.getElementById('al-view-notes');
  const viewHeaderTitle = document.getElementById('al-vh-title');
  const viewMeta = document.getElementById('al-vh-meta');
  const viewImgCount = document.getElementById('al-view-img-count');
  const openEditFromView = document.getElementById('al-open-edit');
  const editModal = document.getElementById('al-edit-modal');
  const editForm = document.getElementById('al-edit-form');
  const deleteModal = document.getElementById('al-delete-modal');
  const deleteText = document.getElementById('al-delete-text');
  const deleteConfirm = document.getElementById('al-delete-confirm');

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
            `<button class="tbl-btn" data-view title="View"><i class="fa-regular fa-eye"></i></button>`+
            `<button class="tbl-btn" data-edit title="Edit"><i class="fa-regular fa-pen-to-square"></i></button>`+
            `<button class="tbl-btn danger" data-delete title="Delete"><i class="fa-regular fa-trash-can"></i></button>`+
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
    const btn = e.target.closest('button'); if(!btn) return;
    const tr = btn.closest('tr'); const id = tr?.dataset.id; if(!id) return;
    if(btn.hasAttribute('data-view')){ loadView(id); }
    else if(btn.hasAttribute('data-edit')){ loadEdit(id); }
    else if(btn.hasAttribute('data-delete')){ confirmDelete(id, tr); }
  });

  function loadView(id){
    fetch(`/admin/listings/${id}`, { headers: {'Accept':'application/json'} })
      .then(r=>r.json())
      .then(json=>{
        const d = json.data; state.currentId = d.id;
        // Header
        if(viewHeaderTitle) viewHeaderTitle.textContent = d.title || '—';
        if(viewMeta){
          viewMeta.innerHTML = '';
          const cond = document.createElement('span'); cond.className='vh-pill badge-cond'; cond.textContent = (d.condition||'—').toUpperCase(); viewMeta.appendChild(cond);
          const weight = document.createElement('span'); weight.className='vh-pill'; weight.textContent = (d.estimated_weight ?? '—') + ' kg'; viewMeta.appendChild(weight);
          const idpill = document.createElement('span'); idpill.className='vh-pill'; idpill.textContent = '#'+d.id; viewMeta.appendChild(idpill);
          const loc = document.createElement('span'); loc.className='vh-pill geo'; loc.textContent = d.location? ( (d.location.lat ?? '—')+','+(d.location.lng ?? '—') ) : 'No location'; viewMeta.appendChild(loc);
        }
        // Summary list
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
        if(viewNotes){ viewNotes.textContent = d.notes || '—'; }
        // Images
        viewImages.innerHTML = '';
        d.images.forEach(img=>{
          const div = document.createElement('div');
          div.className = 'img-box';
          div.innerHTML = `<img src="${img.url}" alt="Image ${img.id}" />`;
          viewImages.appendChild(div);
        });
        if(viewImgCount){ viewImgCount.textContent = d.images.length; }
        openModal(viewModal);
      });
  }
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

  editForm?.addEventListener('submit', e=>{
    e.preventDefault(); if(!state.currentId) return;
    // sync hidden fields
    ef.keepField.value = state.keepImages.join(',');
    ef.removeField.value = state.removeImages.join(',');
    const formData = new FormData(editForm);
    fetch(`/admin/listings/${state.currentId}`, {
      method: 'POST',
      headers: { 'X-HTTP-Method-Override': 'PATCH' },
      body: formData
    }).then(r=>r.json())
      .then(json=>{
        closeModal(editModal);
        fetchList();
      }).catch(err=>console.error(err));
  });

  function confirmDelete(id, tr){
    state.currentId = id;
    deleteText.textContent = `Delete listing #${id}? This can be restored later (soft delete).`;
    openModal(deleteModal);
    deleteConfirm.onclick = () => doDelete(id, tr);
  }

  function doDelete(id, tr){
    fetch(`/admin/listings/${id}`, { method: 'POST', headers: { 'X-HTTP-Method-Override':'DELETE','Accept':'application/json' } })
      .then(r=>r.json())
      .then(()=>{
        tr?.remove();
        closeModal(deleteModal);
        fetchList();
      });
  }

  // Initial fetch after DOM ready (replace server-provided table soon)
  fetchList();
})();
