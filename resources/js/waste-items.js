// waste-items.js
// Handles view, edit and delete modals for waste items listing.

(function(){
  const overlay = document.getElementById('modalOverlay');
  if(!overlay) return; // page not present

  // Elements
  const viewModal = document.getElementById('viewModal');
  const editModal = document.getElementById('editModal');
  const deleteModal = document.getElementById('deleteModal');

  const viewLoading = document.getElementById('viewLoading');
  const viewContent = document.getElementById('viewContent');
  const viewCondition = document.getElementById('viewCondition');
  const viewWeight = document.getElementById('viewWeight');
  const viewNotes = document.getElementById('viewNotes');
  const viewImages = document.getElementById('viewImages');
  const openEditFromView = document.getElementById('openEditFromView');

  const editForm = document.getElementById('editForm');
  const editId = document.getElementById('editId');
  const editTitle = document.getElementById('editTitle');
  const editCondition = document.getElementById('editCondition');
  const editWeight = document.getElementById('editWeight');
  const editNotes = document.getElementById('editNotes');
  const editSubmitBtn = document.getElementById('editSubmitBtn');

  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

  const csrf = window.csrfToken;
  const base = (window.wasteItemRoutes && window.wasteItemRoutes.base) || '/waste-items';

  let currentId = null;
  let lastLoadedData = null;
  let deleteTargetId = null;

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
    if(materialsEl) materialsEl.textContent = data.materials_count ?? (data.materials ? data.materials.length : 0) ?? 0;
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
    (data.images || []).forEach(img => {
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
    editId.value = data.id;
    editTitle.value = data.title || '';
    editCondition.value = data.condition || 'good';
    editWeight.value = data.estimated_weight ?? '';
    editNotes.value = data.notes || '';
  }

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
    const id = editId.value;
    const formData = new FormData(editForm);
    // convert to JSON object
    const payload = {};
    formData.forEach((v,k)=>{ payload[k]=v; });
    editSubmitBtn.disabled = true;
    editSubmitBtn.classList.add('opacity-50');
    try {
      const res = await fetch(`${base}/${id}`, {
        method:'PUT',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
        body: JSON.stringify(payload)
      });
      if(!res.ok) throw new Error('Update failed');
      const data = await res.json();
      updateCardDom(id, data.data || payload);
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

  document.addEventListener('click', e => {
    const viewBtn = e.target.closest('.btn-view');
    if(viewBtn){ e.preventDefault(); handleView(viewBtn.getAttribute('data-id')); }
    const editBtn = e.target.closest('.btn-edit');
    if(editBtn){ e.preventDefault(); handleEditOpen(editBtn.getAttribute('data-id')); }
    const deleteBtn = e.target.closest('.btn-delete');
    if(deleteBtn){ e.preventDefault(); handleDelete(deleteBtn.getAttribute('data-id')); }
  });

  openEditFromView?.addEventListener('click', e => { e.preventDefault(); handleEditFromView(); });
})();
