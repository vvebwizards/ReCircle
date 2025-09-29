// waste-items.js
// Handles view, edit and delete modals for waste items listing.

(function(){
  const overlay = document.getElementById('modalOverlay');
  if(!overlay) return; // page not present

  // Elements
  const viewModal = document.getElementById('viewModal');
  const editModal = document.getElementById('editModal');
  const photosModal = document.getElementById('photosModal');
  const deleteModal = document.getElementById('deleteModal');
  const createModal = document.getElementById('createModal');

  const viewLoading = document.getElementById('viewLoading');
  const viewContent = document.getElementById('viewContent');
  const viewCondition = document.getElementById('viewCondition');
  const viewWeight = document.getElementById('viewWeight');
  // removed unused: const viewNotes (query again where needed for freshness)
  const viewImages = document.getElementById('viewImages');
  // removed unused: openEditFromView (accessed via event delegation by id)
  const viewAllPhotosBtn = document.getElementById('viewAllPhotosBtn');
  // Photos lightbox elements
  const photosLoader = document.getElementById('photosLoader');
  const photosMainWrap = document.getElementById('photosMainWrap');
  const photosMainImg = document.getElementById('photosMainImage');
  const photosThumbs = document.getElementById('photosThumbs');
  const photosCaption = document.getElementById('photosCaption');
  const photosError = document.getElementById('photosError');

  let lightboxImages = [];
  let lightboxIndex = 0;

  const editForm = document.getElementById('editForm');
  const editTitle = document.getElementById('editTitle');
  const editCondition = document.getElementById('editCondition');
  const editWeight = document.getElementById('editWeight');
  const editNotes = document.getElementById('editNotes');
  const editLocationLat = document.getElementById('editLocationLat');
  const editLocationLng = document.getElementById('editLocationLng');
  const editImagesExisting = document.getElementById('editImagesExisting');
  const editAddImagesBtn = document.getElementById('editAddImagesBtn');
  const editNewImagesInput = document.getElementById('editNewImages');
  const editKeepImages = document.getElementById('editKeepImages');
  const editRemoveImages = document.getElementById('editRemoveImages');
  const editSubmitBtn = document.getElementById('editSubmitBtn');

  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

  const createForm = document.getElementById('createWasteItemForm');
  const createSubmitBtn = document.getElementById('createSubmitBtn');
  const createTriggerSelector = '.open-create-modal';

  const csrf = window.csrfToken;
  const base = (window.wasteItemRoutes && window.wasteItemRoutes.base) || '/waste-items';
  const filterForm = document.getElementById('filterForm');
  // removed unused: gridEl (DOM always queried on demand for freshness)
  const usingNewFilters = !!window.WasteItemsUI; // new partial-based filter system active

  // Legacy inline filtering system removed (superseded by partial-based WasteItemsUI). Keep a graceful
  // no-op submit preventer only when old system would have been active.
  if(!usingNewFilters){
    filterForm?.addEventListener('submit', e => { e.preventDefault(); });
  }

  let currentId = null;
  let lastLoadedData = null;
  let deleteTargetId = null;
  let editExistingImages = []; // {id,url,order}
  let editRemovedImageIds = [];
  let editNewImages = []; // File objects
  const maxEditImages = 10;

  // Reuse drag/drop logic from create page (lightweight subset)
  let createFiles = [];
  const createDropzone = document.getElementById('imageDropzone');
  const createFileInput = document.getElementById('images');
  const createPreviewList = document.getElementById('imagePreviewList');
  const maxCreateFiles = 10;

  function renderCreatePreviews(){
    if(!createPreviewList) return;
    createPreviewList.innerHTML = '';
    createFiles.forEach((file, index) => {
      const wrap = document.createElement('div');
      wrap.className = 'preview-item';
      const img = document.createElement('img');
      img.className = 'preview-thumb';
      const reader = new FileReader();
      reader.onload = e => img.src = e.target.result;
      reader.readAsDataURL(file);
      const meta = document.createElement('div');
      meta.className = 'preview-meta';
      meta.innerHTML = `<strong>${index===0?'Primary • ':''}</strong>${file.name}<br><small>${(file.size/1024).toFixed(1)} KB</small>`;
      const actions = document.createElement('div');
      actions.className = 'preview-actions';
      const remove = document.createElement('button');
      remove.type='button';
      remove.className='btn btn-sm btn-danger';
      remove.innerHTML='<i class="fa-solid fa-xmark"></i>';
      remove.onclick=()=>{ createFiles.splice(index,1); syncCreateInput(); renderCreatePreviews(); };
      actions.appendChild(remove);
      if(index>0){
        const up=document.createElement('button');
        up.type='button';up.className='btn btn-sm btn-secondary';up.innerHTML='<i class="fa-solid fa-arrow-up"></i>';
        up.onclick=()=>{ const tmp=createFiles[index-1];createFiles[index-1]=createFiles[index];createFiles[index]=tmp;syncCreateInput();renderCreatePreviews();};
        actions.appendChild(up);
      }
      wrap.appendChild(img);wrap.appendChild(meta);wrap.appendChild(actions);createPreviewList.appendChild(wrap);
    });
  }
  function syncCreateInput(){
    if(!createFileInput) return;
    const dt = new DataTransfer();
    createFiles.forEach(f=>dt.items.add(f));
    createFileInput.files = dt.files;
  }
  function addCreateFiles(list){
    Array.from(list).forEach(f=>{
      if(!f.type.startsWith('image/')) return;
      if(f.size > 2*1024*1024) return showToast(`${f.name} >2MB skipped`,'error');
      if(createFiles.length>=maxCreateFiles) return;
      createFiles.push(f);
    });
    syncCreateInput();
    renderCreatePreviews();
  }
  if(createDropzone){
    createDropzone.addEventListener('click', ()=>createFileInput?.click());
    createDropzone.addEventListener('keydown', e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); createFileInput?.click(); }});
    ['dragenter','dragover'].forEach(ev=>createDropzone.addEventListener(ev,e=>{e.preventDefault();createDropzone.classList.add('drag-active');}));
    ;['dragleave','drop'].forEach(ev=>createDropzone.addEventListener(ev,e=>{e.preventDefault();if(ev==='dragleave'&&e.target!==createDropzone)return;createDropzone.classList.remove('drag-active');}));
    createDropzone.addEventListener('drop', e=>{ if(e.dataTransfer?.files) addCreateFiles(e.dataTransfer.files); });
    createFileInput?.addEventListener('change',()=>addCreateFiles(createFileInput.files));
  }

  function openModal(modal){
    overlay.classList.add('active');
    modal.classList.remove('hidden');
    modal.focus?.();
  }
  function closeModal(modal){
    modal.classList.add('hidden');
    const anyOpen = Array.from(overlay.querySelectorAll('.modal')).some(m => !m.classList.contains('hidden'));
    if(!anyOpen){
      overlay.classList.remove('active');
    }
  }
  function closeAll(){
    overlay.querySelectorAll('.modal').forEach(m=>m.classList.add('hidden'));
    overlay.classList.remove('active');
  }

  overlay.addEventListener('click', e => {
    if(e.target === overlay){
      closeAll();
    }
  });
  overlay.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-close');
      const target = document.getElementById(id);
      if(target) closeModal(target);
    });
  });

  document.addEventListener('keydown', e => {
    if(e.key === 'Escape') closeAll();
    if(!photosModal || photosModal.classList.contains('hidden')) return;
    if(e.key === 'ArrowRight') navigateLightbox(1);
    if(e.key === 'ArrowLeft') navigateLightbox(-1);
  });

  function showToast(msg, type='info'){
    const t = document.createElement('div');
    t.className = 'toast ' + (type==='error'?'error': type==='success'?'success':'');
    t.innerHTML = '<span>'+msg+'</span>';
    document.body.appendChild(t);
    setTimeout(()=>{t.classList.add('fade');},10);
    setTimeout(()=>{t.remove();},4000);
  }

  async function fetchItem(id){
    try {
      const res = await fetch(`${base}/${id}`, { headers: { 'Accept':'application/json' } });
      if(!res.ok) throw new Error('Failed loading waste item');
      const data = await res.json();
      return data.data;
    } catch(err){
      showToast(err.message || 'Error', 'error');
      throw err;
    }
  }

  function populateView(data){
    // Condition badge styling
    if(viewCondition){
      const cond = (data.condition || '').toLowerCase();
      viewCondition.classList.remove('condition-good','condition-fixable','condition-scrap');
      if(cond) viewCondition.classList.add('condition-'+cond);
      // inject icon if not present
      if(!viewCondition.querySelector('i')){
        const ic = document.createElement('i');
        ic.className='fa-solid fa-circle';
        viewCondition.prepend(ic);
      }
      const iconMap = { good:'fa-check', fixable:'fa-wrench', scrap:'fa-recycle' };
      const iEl = viewCondition.querySelector('i');
      if(iEl){
        iEl.className = 'fa-solid ' + (iconMap[cond] || 'fa-circle');
      }
      viewCondition.lastChild && (viewCondition.lastChild.nodeType===3 ? viewCondition.lastChild.remove() : null); // remove stray text node
      viewCondition.appendChild(document.createTextNode(cond ? cond.toUpperCase() : '—'));
    }
    viewWeight.textContent = data.estimated_weight ? `${data.estimated_weight} kg` : '';
    const idEl = document.getElementById('viewId'); if(idEl) idEl.textContent = data.id ? `#${data.id}` : '—';
    const titleEl = document.getElementById('viewTitle'); if(titleEl) titleEl.textContent = data.title || '—';
    const createdEl = document.getElementById('viewCreated'); if(createdEl) createdEl.textContent = data.created_at ? formatDate(data.created_at) : '—';
    const updatedEl = document.getElementById('viewUpdated'); if(updatedEl) updatedEl.textContent = data.updated_at ? formatDate(data.updated_at) : '—';
    const materialsEl = document.getElementById('viewMaterials'); if(materialsEl) materialsEl.textContent = (data.materials_count != null) ? data.materials_count : (data.materials ? data.materials.length : 0) ?? 0;
    // location badges
    const locPill = document.getElementById('viewLocation');
    const locDetail = document.getElementById('viewLocationDetail');
    let locDisplay = 'No location';
    if(data.location && (data.location.lat != null || data.location.lng != null)){
      const lat = data.location.lat ?? '—';
      const lng = data.location.lng ?? '—';
      locDisplay = `${lat}, ${lng}`;
    }
    if(locPill) locPill.textContent = locDisplay;
    if(locDetail) locDetail.textContent = locDisplay;
    // notes
    const notesEl = document.getElementById('viewNotes'); if(notesEl) notesEl.textContent = (data.notes && data.notes.trim().length) ? data.notes : '—';
    // images
    viewImages.innerHTML = '';
    const imgs = (data.images || []).sort((a,b)=> (a.order??0)-(b.order??0));
    imgs.forEach(img => {
      const el = document.createElement('img');
      el.src = img.url || img.path;
      el.alt = data.title || '';
      el.loading = 'lazy';
      viewImages.appendChild(el);
    });
    const imgCount = document.getElementById('viewImagesCount'); if(imgCount) imgCount.textContent = imgs.length;
  }

  /* ============================= */
  /* Photos Lightbox               */
  /* ============================= */
  function openPhotosLightbox(id){
    if(!photosModal) return;
    photosError?.classList.add('hidden');
    photosMainWrap?.classList.add('hidden');
    photosLoader?.classList.remove('hidden');
    photosThumbs.innerHTML = '';
    photosCaption.textContent='';
    lightboxImages = []; lightboxIndex = 0;
    openModal(photosModal);
    fetchItem(id).then(data => {
      const imgs = (data.images||[]).sort((a,b)=>(a.order??0)-(b.order??0));
      lightboxImages = imgs.map(i => i.url || i.path);
      if(!lightboxImages.length){
        photosLoader?.classList.add('hidden');
        photosError?.classList.remove('hidden');
        photosError && (photosError.querySelector('span').textContent = 'No photos to display.');
        return;
      }
      // Build thumbnails
      lightboxImages.forEach((src, idx) => {
        const t = document.createElement('img');
        t.src = src; t.alt = 'Photo '+(idx+1);
        t.loading='lazy';
        t.dataset.index = idx;
        photosThumbs.appendChild(t);
      });
      photosThumbs.addEventListener('click', onThumbClick, { once:true }); // delegate once to attach listener
      setLightboxIndex(0);
      photosLoader?.classList.add('hidden');
      photosMainWrap?.classList.remove('hidden');
    }).catch(()=>{
      photosLoader?.classList.add('hidden');
      photosError?.classList.remove('hidden');
    });
  }

  function onThumbClick(e){
    const img = e.target.closest('img[data-index]');
    if(!img) return;
    const idx = parseInt(img.dataset.index,10);
    if(!isNaN(idx)) setLightboxIndex(idx);
    // keep delegation active
    photosThumbs.addEventListener('click', onThumbClick);
  }

  function setLightboxIndex(idx){
    if(!lightboxImages.length) return;
    lightboxIndex = (idx+lightboxImages.length)%lightboxImages.length;
    const src = lightboxImages[lightboxIndex];
    photosMainImg.src = src + (src.includes('?')?'&':'?') + 'v=' + Date.now();
    photosMainImg.classList.remove('hidden');
    // update active thumb
    photosThumbs.querySelectorAll('img').forEach(t => t.classList.toggle('active', parseInt(t.dataset.index,10)===lightboxIndex));
    photosCaption.textContent = `Image ${lightboxIndex+1} of ${lightboxImages.length}`;
    // hide nav if single image
    const prevBtn = photosMainWrap.querySelector('.lb-nav.prev');
    const nextBtn = photosMainWrap.querySelector('.lb-nav.next');
    if(prevBtn) prevBtn.style.display = lightboxImages.length>1 ? '' : 'none';
    if(nextBtn) nextBtn.style.display = lightboxImages.length>1 ? '' : 'none';
  }

  function navigateLightbox(delta){
    if(!lightboxImages.length) return;
    setLightboxIndex(lightboxIndex + delta);
  }

  photosMainWrap?.addEventListener('click', e => {
    if(e.target.classList.contains('lb-nav')) return; // handled separately
  });
  photosMainWrap?.querySelector('.lb-nav.prev')?.addEventListener('click', ()=>navigateLightbox(-1));
  photosMainWrap?.querySelector('.lb-nav.next')?.addEventListener('click', ()=>navigateLightbox(1));

  // View modal 'See all photos' button
  viewAllPhotosBtn?.addEventListener('click', () => {
    if(lastLoadedData?.id){
      openPhotosLightbox(lastLoadedData.id);
    }
  });

  function formatDate(str){
    try { return new Date(str).toLocaleString(); } catch { return str; }
  }

  function populateEdit(data){
    editTitle.value = data.title || '';
    editCondition.value = data.condition || 'good';
    editWeight.value = data.estimated_weight ?? '';
    editNotes.value = data.notes || '';
    if(editLocationLat) editLocationLat.value = (data.location && data.location.lat!=null) ? data.location.lat : '';
    if(editLocationLng) editLocationLng.value = (data.location && data.location.lng!=null) ? data.location.lng : '';
    // images
    editExistingImages = (data.images || []).map(img => ({ id: img.id, url: img.url || img.path, order: img.order ?? 0 }));
    editRemovedImageIds = [];
    editNewImages = [];
    renderEditImages();
  }

  function renderEditImages(){
    if(!editImagesExisting) return;
    editImagesExisting.innerHTML = '';
    // combined array with markers existing/new
    const combined = [...editExistingImages.sort((a,b)=>a.order-b.order), ...editNewImages.map((file, idx)=>({ tempId: 'new_'+idx, file, url: URL.createObjectURL(file), order: 1000+idx }))];
    combined.slice(0, maxEditImages); // ensure limit
    combined.forEach((img, index) => {
      const wrap = document.createElement('div');
      wrap.className = 'preview-item';
      wrap.draggable = true;
      wrap.dataset.index = index;
      const imageEl = document.createElement('img');
      imageEl.className = 'preview-thumb';
      imageEl.src = img.url;
      const meta = document.createElement('div');
      meta.className = 'preview-meta';
      const isPrimary = index === 0;
      meta.innerHTML = `<strong>${isPrimary ? 'Primary • ' : ''}</strong>${img.file ? img.file.name : ('ID '+img.id)}`;
      const actions = document.createElement('div');
      actions.className = 'preview-actions';
      const removeBtn = document.createElement('button');
      removeBtn.type='button'; removeBtn.className='btn btn-sm btn-danger'; removeBtn.innerHTML='<i class="fa-solid fa-xmark"></i>';
      removeBtn.onclick = () => {
        if(img.id){
          editExistingImages = editExistingImages.filter(e => e.id !== img.id);
          editRemovedImageIds.push(img.id);
        } else if(img.tempId){
          const idx = editNewImages.indexOf(img.file);
            if(idx>-1) editNewImages.splice(idx,1);
        }
        renderEditImages();
      };
      actions.appendChild(removeBtn);
      if(index>0){
        const upBtn = document.createElement('button');
        upBtn.type='button'; upBtn.className='btn btn-sm btn-secondary'; upBtn.innerHTML='<i class="fa-solid fa-arrow-up"></i>';
        upBtn.onclick=()=>{ moveEditImage(index, index-1); };
        actions.appendChild(upBtn);
      }
      if(index < combined.length -1){
        const downBtn = document.createElement('button');
        downBtn.type='button'; downBtn.className='btn btn-sm btn-secondary'; downBtn.innerHTML='<i class="fa-solid fa-arrow-down"></i>';
        downBtn.onclick=()=>{ moveEditImage(index, index+1); };
        actions.appendChild(downBtn);
      }
      wrap.appendChild(imageEl); wrap.appendChild(meta); wrap.appendChild(actions);
      // drag events
      wrap.addEventListener('dragstart', e=>{ e.dataTransfer.setData('text/plain', index); wrap.classList.add('dragging'); });
      wrap.addEventListener('dragend', ()=> wrap.classList.remove('dragging'));
      wrap.addEventListener('dragover', e=>{ e.preventDefault(); wrap.classList.add('drag-over'); });
      wrap.addEventListener('dragleave', ()=> wrap.classList.remove('drag-over'));
      wrap.addEventListener('drop', e=>{ e.preventDefault(); wrap.classList.remove('drag-over'); const from = parseInt(e.dataTransfer.getData('text/plain')); const to = index; moveEditImage(from,to); });
      editImagesExisting.appendChild(wrap);
    });
    // update hidden fields
    editKeepImages.value = editExistingImages.map(e=>e.id).join(',');
    editRemoveImages.value = editRemovedImageIds.join(',');
  }

  function moveEditImage(from, to){
    // Construct combined, move, then split back
    const combined = [...editExistingImages.sort((a,b)=>a.order-b.order), ...editNewImages.map((file, idx)=>({ tempId:'new_'+idx, file, url: URL.createObjectURL(file), order: 1000+idx }))];
    if(to<0 || to>=combined.length) return;
    const item = combined.splice(from,1)[0];
    combined.splice(to,0,item);
    // Re-split
    const newExisting = [];
    const newNew = [];
    combined.forEach((c,i)=>{
      if(c.id){ c.order = i; newExisting.push(c); }
      else if(c.tempId){ newNew.push(c.file); }
    });
    editExistingImages = newExisting;
    editNewImages = newNew;
    renderEditImages();
  }

  editAddImagesBtn?.addEventListener('click', ()=> editNewImagesInput?.click());
  editNewImagesInput?.addEventListener('change', () => {
    if(!editNewImagesInput.files) return;
    Array.from(editNewImagesInput.files).forEach(f => {
      if(!f.type.startsWith('image/')) return;
      if(f.size > 2*1024*1024) return showToast(`${f.name} >2MB skipped`,'error');
      const totalCount = editExistingImages.length + editNewImages.length;
      if(totalCount >= maxEditImages) return;
      editNewImages.push(f);
    });
    editNewImagesInput.value = '';
    renderEditImages();
  });

  function updateCardDom(id, data){
    // Simplest: refresh entire grid when new filter system active; avoids DOM drift
    if(usingNewFilters && window.WasteItemsUI){
      window.WasteItemsUI.updateContent(window.location.href, { pushState:false });
      return;
    }
    // Legacy fallback (kept if old markup still in use)
    const card = document.querySelector(`.material-card[data-id='${id}']`);
    if(!card) return;
    const nameEl = card.querySelector('.material-name'); if(nameEl) nameEl.textContent = data.title;
    const badgeEl = card.querySelector('.material-badge'); if(badgeEl) badgeEl.textContent = (data.condition||'').charAt(0).toUpperCase()+ (data.condition||'').slice(1);
    const weightLi = card.querySelector('.mc-meta li:first-child');
    if(weightLi && data.estimated_weight!=null) weightLi.innerHTML = `<i class="fa-solid fa-weight-hanging"></i>${data.estimated_weight} kg`;
    const notesEl = card.querySelector('.mc-notes'); if(notesEl) notesEl.textContent = (data.notes||'').slice(0,90);
    if(data.images){
      const countEl = card.querySelector('.card-img-count .count-val'); if(countEl) countEl.textContent = data.images.length;
      const primary = [...data.images].sort((a,b)=>(a.order??0)-(b.order??0))[0];
      if(primary){
        const imgEl = card.querySelector('img.card-primary-img');
        if(imgEl) imgEl.src = primary.url + '?v=' + Date.now();
      }
    }
  }

  async function handleView(id){
    currentId = id;
    const errorEl = document.getElementById('viewError');
    if(errorEl) errorEl.classList.add('hidden');
    if(viewLoading) viewLoading.classList.remove('hidden');
    if(viewContent) viewContent.classList.add('hidden');
    openModal(viewModal);
    try {
      const data = await fetchItem(id);
      lastLoadedData = data;
      populateView(data);
      if(viewLoading) viewLoading.classList.add('hidden');
      if(viewContent) viewContent.classList.remove('hidden');
    } catch(err){
      if(viewLoading) viewLoading.classList.add('hidden');
      const errorEl2 = document.getElementById('viewError');
      if(errorEl2) errorEl2.classList.remove('hidden');
    }
  }

  function handleEditFromView(){
    // If data not yet loaded but we have an id, fetch first
    if(!lastLoadedData){
      if(currentId){
        fetchItem(currentId).then(data => { lastLoadedData = data; populateEdit(data); closeModal(viewModal); openModal(editModal); }).catch(()=>{});
      }
      return;
    }
    populateEdit(lastLoadedData);
    closeModal(viewModal);
    openModal(editModal);
  }

  async function handleEditOpen(id){
    currentId = id;
    try {
      const data = await fetchItem(id);
      lastLoadedData = data;
      populateEdit(data);
      openModal(editModal);
  } catch{}
  }

  editForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const id = currentId;
    // Build multipart form data to allow file uploads
    const formData = new FormData();
    formData.append('title', editTitle.value);
    formData.append('condition', editCondition.value);
    if(editWeight.value!== '') formData.append('estimated_weight', editWeight.value);
    if(editNotes.value!== '') formData.append('notes', editNotes.value);
    const latVal = editLocationLat?.value;
    const lngVal = editLocationLng?.value;
    if(latVal!=='' || lngVal!==''){
      formData.append('location[lat]', latVal || '');
      formData.append('location[lng]', lngVal || '');
    }
    // images ordering & removals
    formData.append('keep_images', editKeepImages.value);
    formData.append('remove_images', editRemoveImages.value);
    editNewImages.forEach(f => formData.append('new_images[]', f));
    editSubmitBtn.disabled = true;
    editSubmitBtn.classList.add('opacity-50');
    try {
      const res = await fetch(`${base}/${id}`, {
        method:'POST', // Laravel can treat this as PUT with _method
        headers: { 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
        body: (()=>{ formData.append('_method','PUT'); return formData; })()
      });
      if(!res.ok) throw new Error('Update failed');
      const data = await res.json();
      updateCardDom(id, data.data || {});
      showToast('Updated successfully','success');
      closeModal(editModal);
    } catch(err){ // keep toast message
      showToast(err.message || 'Error updating','error');
    } finally {
      editSubmitBtn.disabled = false;
      editSubmitBtn.classList.remove('opacity-50');
    }
  });

  function handleDelete(id){
    deleteTargetId = id;
    openModal(deleteModal);
  }

  confirmDeleteBtn?.addEventListener('click', async () => {
    if(!deleteTargetId) return;
    const id = deleteTargetId;
    confirmDeleteBtn.disabled = true;
    try {
      const res = await fetch(`${base}/${id}`, {
        method:'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
      });
      if(!res.ok) throw new Error('Delete failed');
      const card = document.querySelector(`.material-card[data-id='${id}']`);
      if(card){ card.style.transition='opacity .25s'; card.style.opacity='0'; setTimeout(()=>card.remove(),260); }
      showToast('Deleted','success');
      closeModal(deleteModal);
    } catch(_e){ // keep toast message (unused variable renamed for lint)
      showToast(_e.message || 'Error deleting','error');
    } finally {
      confirmDeleteBtn.disabled = false;
      deleteTargetId = null;
    }
  });

  function openCreate(){ openModal(createModal); }

  document.addEventListener('click', e => {
    const viewBtn = e.target.closest('.btn-view');
    if(viewBtn){ e.preventDefault(); handleView(viewBtn.getAttribute('data-id')); }
    const editBtn = e.target.closest('.btn-edit');
    if(editBtn){ e.preventDefault(); handleEditOpen(editBtn.getAttribute('data-id')); }
    const deleteBtn = e.target.closest('.btn-delete');
    if(deleteBtn){ e.preventDefault(); handleDelete(deleteBtn.getAttribute('data-id')); }
    if(e.target.closest(createTriggerSelector)){ e.preventDefault(); openCreate(); }
    const photosBtn = e.target.closest('.view-photos');
    if(photosBtn){
      e.preventDefault();
      const card = photosBtn.closest('.material-card');
      if(card){
        const id = card.getAttribute('data-id');
        openPhotosLightbox(id);
      }
    }
    if(e.target.closest('#openEditFromView')){
      e.preventDefault();
      handleEditFromView();
    }
  });

  // buildCard & other legacy DOM construction helpers removed (superseded by server-rendered partials)

  createForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(createForm);
    createSubmitBtn.disabled = true;
    try {
      const res = await fetch(base, { method:'POST', headers:{ 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' }, body: fd });
      if(res.status===422){
        const j = await res.json();
        showToast('Validation error','error');
        console.error(j);
        return;
      }
      if(!res.ok) throw new Error('Create failed');
      await res.json();
      if(usingNewFilters && window.WasteItemsUI){
        // Refresh sections so new item appears with consistent markup
        await window.WasteItemsUI.updateContent(window.location.href, { pushState:false });
      }
      showToast('Created successfully','success');
      closeModal(createModal);
      createForm.reset();
      createFiles = []; renderCreatePreviews(); syncCreateInput();
    } catch(err){
      showToast(err.message || 'Error creating','error');
    } finally {
      createSubmitBtn.disabled = false;
    }
  });
})();
