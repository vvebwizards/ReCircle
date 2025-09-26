// waste-items.js
// Handles view, edit and delete modals for waste items listing.

(function(){
  const overlay = document.getElementById('modalOverlay');
  if(!overlay) return; // page not present

  // Elements
  const viewModal = document.getElementById('viewModal');
  const editModal = document.getElementById('editModal');
  const deleteModal = document.getElementById('deleteModal');
  const createModal = document.getElementById('createModal');

  const viewLoading = document.getElementById('viewLoading');
  const viewContent = document.getElementById('viewContent');
  const viewCondition = document.getElementById('viewCondition');
  const viewWeight = document.getElementById('viewWeight');
  const viewNotes = document.getElementById('viewNotes');
  const viewImages = document.getElementById('viewImages');
  const openEditFromView = document.getElementById('openEditFromView');

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
  const gridEl = document.querySelector('.materials-grid');

  // Abort controller for in-flight list requests (live search / filters)
  let listAbortController = null;
  async function fetchList(params){
    const url = new URL(base, window.location.origin);
    Object.entries(params).forEach(([k,v])=>{ if(v!=='' && v!=null) url.searchParams.append(k,v); });
    // cancel any previous request
    if(listAbortController){
      listAbortController.abort();
    }
    listAbortController = new AbortController();
    try {
      const res = await fetch(url.toString(), { headers:{'Accept':'application/json'}, signal: listAbortController.signal });
      if(!res.ok) throw new Error('Failed loading list');
      const data = await res.json();
      return data.data;
    } catch(e){
      if(e.name === 'AbortError') return Promise.reject(e); // silently ignore outside
      showToast(e.message,'error');
      throw e;
    }
  }

  function renderListItems(items){
    if(!gridEl) return;
    gridEl.innerHTML = '';
    if(!items.length){
      gridEl.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">\n<div class='empty-icon'><i class='fa-solid fa-trash'></i></div>\n<h3 class='empty-text'>No waste items found</h3>\n<p>Try adjusting filters.</p>\n<a href='#' class='btn-create open-create-modal' style='display:inline-flex;margin-top:1rem;'><i class='fa-solid fa-plus'></i> Create Waste Item</a>\n</div>`;
      return;
    }
    const frag = document.createDocumentFragment();
    items.forEach(it => {
      const temp = document.createElement('div');
      temp.innerHTML = buildCard({
        id: it.id,
        title: it.title,
        condition: it.condition,
        estimated_weight: it.estimated_weight,
        notes: it.notes,
        images: it.primary_image_url ? [{url: it.primary_image_url, order:0}] : [],
        primary_image_url: it.primary_image_url
      });
      frag.appendChild(temp.firstElementChild);
    });
    gridEl.appendChild(frag);
  }

  function updateStats(stats){
    if(!stats) return;
    const totalEl = document.querySelector('.stat-card:nth-child(1) .stat-number');
    const avgEl = document.querySelector('.stat-card:nth-child(2) .stat-number');
    const condEl = document.querySelector('.stat-card:nth-child(3) .stat-number');
    if(totalEl) totalEl.textContent = stats.total;
    if(avgEl) avgEl.textContent = Number(stats.avgWeight).toFixed(2);
    if(condEl) condEl.textContent = Object.keys(stats.conditionsCount||{}).length;
  }

  function replacePagination(pagination){
    const pagContainer = document.querySelector('.pagination');
    if(!pagContainer || !pagination) return;
    let html = '';
    const { current_page, last_page, prev_page_url, next_page_url } = pagination;
    html += prev_page_url ? `<a href='${prev_page_url}' class='page-link' data-page='${current_page-1}'>&laquo; Previous</a>` : `<span class='page-link disabled'>&laquo; Previous</span>`;
    for(let p=1;p<=last_page;p++){
      if(p===current_page) html += `<span class='page-link active'>${p}</span>`; else html += `<a href='?page=${p}' class='page-link' data-page='${p}'>${p}</a>`;
    }
    html += next_page_url ? `<a href='${next_page_url}' class='page-link' data-page='${current_page+1}'>Next &raquo;</a>` : `<span class='page-link disabled'>Next &raquo;</span>`;
    pagContainer.innerHTML = html;
  }

  async function applyFilters(pushState=true){
    if(!filterForm) return;
    const formData = new FormData(filterForm);
    const params = {};
    formData.forEach((v,k)=> params[k]=v);
    params.page = params.page || 1;
    const data = await fetchList(params);
    renderListItems(data.items || []);
    updateStats(data.stats);
    replacePagination(data.pagination);
    if(pushState){
      const url = new URL(window.location.href);
      Object.keys(params).forEach(k=>{ if(params[k]) url.searchParams.set(k, params[k]); else url.searchParams.delete(k); });
      window.history.replaceState({}, '', url.toString());
    }
  }

  // change handler (non-text inputs like selects)
  filterForm?.addEventListener('change', e => {
    if(e.target.id === 'search') return; // search handled by keyup
    applyFilters();
  });
  // live search (keyup debounce + abortable fetch)
  if(filterForm){
    const searchInput = filterForm.querySelector('#search');
    if(searchInput){
      let debounceTimer = null;
      searchInput.addEventListener('keyup', () => {
        if(debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(()=>{
          // reset page when initiating a new search
          const pageField = filterForm.querySelector('input[name="page"]');
          if(pageField) pageField.value = 1;
          applyFilters();
        }, 320);
      });
    }
  }
  filterForm?.addEventListener('submit', e => { e.preventDefault(); applyFilters(); });
  document.addEventListener('click', e => {
    const pagLink = e.target.closest('.pagination a.page-link');
    if(pagLink && pagLink.dataset.page){ e.preventDefault();
      const page = pagLink.dataset.page;
      const fd = new FormData(filterForm); fd.set('page', page); // stash temporarily
      filterForm.querySelector('input[name="page"]') || filterForm.insertAdjacentHTML('beforeend', `<input type='hidden' name='page' value='${page}'>`);
      filterForm.querySelector('input[name="page"]').value = page;
      applyFilters();
    }
  });

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
    viewCondition.textContent = data.condition ? data.condition.toUpperCase() : '—';
    viewWeight.textContent = data.estimated_weight ? `${data.estimated_weight} kg` : '';
    const idEl = document.getElementById('viewId');
    if(idEl) idEl.textContent = data.id ? `#${data.id}` : '—';
    const titleEl = document.getElementById('viewTitle');
    if(titleEl) titleEl.textContent = data.title || '—';
    const createdEl = document.getElementById('viewCreated');
    if(createdEl) createdEl.textContent = data.created_at ? formatDate(data.created_at) : '—';
    const updatedEl = document.getElementById('viewUpdated');
    if(updatedEl) updatedEl.textContent = data.updated_at ? formatDate(data.updated_at) : '—';
    const materialsEl = document.getElementById('viewMaterials');
    if(materialsEl) materialsEl.textContent = (data.materials_count != null) ? data.materials_count : (data.materials ? data.materials.length : 0) ?? 0;
    const locEl = document.getElementById('viewLocation');
    if(locEl){
      if(data.location && (data.location.lat || data.location.lng)) {
        locEl.textContent = `${data.location.lat ?? '—'}, ${data.location.lng ?? '—'}`;
      } else {
        locEl.textContent = 'No location';
      }
    }
    viewNotes.textContent = data.notes || '—';
    viewImages.innerHTML = '';
    (data.images || []).sort((a,b)=> (a.order??0)-(b.order??0)).forEach(img => {
      const el = document.createElement('img');
      el.src = img.url || img.path;
      el.alt = data.title;
      viewImages.appendChild(el);
    });
  }

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
    const card = document.querySelector(`.material-card[data-id='${id}']`);
    if(!card) return;
    card.querySelector('.material-name').textContent = data.title;
    card.querySelector('.material-badge').textContent = (data.condition||'').charAt(0).toUpperCase()+ (data.condition||'').slice(1);
    // weight
    const metaItems = card.querySelectorAll('.meta-item span');
    if(metaItems[0]) metaItems[0].textContent = (data.estimated_weight ?? '—') + ' kg';
    // notes
    const desc = card.querySelector('.material-description');
    if(desc) desc.textContent = (data.notes || '').slice(0,100);
    // images (primary + count)
    if(data.images){
      const countEl = card.querySelector('.card-img-count .count-val');
      if(countEl) countEl.textContent = data.images.length;
      const primary = [...data.images].sort((a,b)=> (a.order??0)-(b.order??0))[0];
      const imgEl = card.querySelector('img.card-primary-img');
      const fallback = card.querySelector('.card-primary-fallback');
      if(primary){
        if(imgEl){
          imgEl.src = primary.url + '?v=' + Date.now();
        } else if(fallback){
          const newImg = document.createElement('img');
          newImg.className='card-primary-img';
          newImg.style.cssText='width:100%;height:100%;object-fit:cover;';
          newImg.src = primary.url + '?v=' + Date.now();
          fallback.replaceWith(newImg);
        }
      } else {
        if(imgEl){
          const fb = document.createElement('div');
          fb.className='card-primary-fallback';
          fb.style.cssText='font-size:0.85rem;color:#888;display:flex;flex-direction:column;align-items:center;gap:0.25rem;';
          fb.innerHTML = "<i class='fa-solid fa-image' style='font-size:1.4rem;'></i><span>No Image</span>";
          imgEl.replaceWith(fb);
        }
      }
    }
  }

  async function handleView(id){
    currentId = id;
    viewLoading.classList.remove('hidden');
    viewContent.classList.add('hidden');
    openModal(viewModal);
    try {
      const data = await fetchItem(id);
      lastLoadedData = data;
      populateView(data);
      viewLoading.classList.add('hidden');
      viewContent.classList.remove('hidden');
    } catch(err){
      closeModal(viewModal);
    }
  }

  function handleEditFromView(){
    if(!lastLoadedData) return;
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
    } catch(err){}
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
    } catch(err){
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
    } catch(err){
      showToast(err.message || 'Error deleting','error');
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
  });

  function buildCard(item){
    const primary = item.primary_image_url || (item.images && [...item.images].sort((a,b)=>(a.order??0)-(b.order??0))[0] && [...item.images].sort((a,b)=>(a.order??0)-(b.order??0))[0].url);
    return `<div class="material-card" data-id="${item.id}">
      <div class="material-image-wrapper" style="height:140px;overflow:hidden;position:relative;border-radius:4px 4px 0 0;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
        <div class="card-img-count" style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,.55);color:#fff;font-size:10px;padding:2px 4px;border-radius:3px;z-index:2;">imgs: <span class='count-val'>${(item.images?item.images.length:0)}</span></div>
        ${primary ? `<img src="${primary}" alt="${item.title}" class="card-primary-img" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x240?text=Image';">` : `<div class='card-primary-fallback' style='font-size:.85rem;color:#888;display:flex;flex-direction:column;align-items:center;gap:.25rem;'><i class='fa-solid fa-image' style='font-size:1.4rem;'></i><span>No Image</span></div>`}
      </div>
      <div class="material-content" style="padding-top:0.8rem;">
        <div class="material-header">
          <h3 class="material-name">${item.title}</h3>
          <span class="material-badge">${(item.condition||'').charAt(0).toUpperCase()+ (item.condition||'').slice(1)}</span>
        </div>
        <div class="material-meta">
          <div class="meta-item"><i class="fa-solid fa-weight-hanging meta-icon"></i><span>${item.estimated_weight ?? '—'} kg</span></div>
          <div class="meta-item"><i class="fa-solid fa-calendar meta-icon"></i><span>${(new Date()).toLocaleDateString()}</span></div>
        </div>
        <p class="material-description">${(item.notes||'').slice(0,100)}</p>
        <div class="material-actions" data-id="${item.id}">
          <a href="#" class="btn-action btn-view" data-id="${item.id}"><i class="fa-solid fa-eye"></i> View</a>
          <a href="#" class="btn-action btn-edit" data-id="${item.id}"><i class="fa-solid fa-edit"></i> Edit</a>
          <form action="#" method="POST" style="display:inline;" class="delete-form" data-id="${item.id}">
            <button type="button" class="btn-action btn-delete" data-id="${item.id}"><i class="fa-solid fa-trash"></i> Delete</button>
          </form>
        </div>
      </div>
    </div>`;
  }

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
      const data = await res.json();
      // Prepend new card
      const grid = document.querySelector('.materials-grid');
      if(grid){
        const temp = document.createElement('div');
        temp.innerHTML = buildCard(data.data);
        const card = temp.firstElementChild;
        grid.prepend(card);
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
