class AdminMaterials {
    constructor() {
        this.currentPage = 1;
        this.totalPages = 1;
        this.currentMaterialId = null;
        this.filters = {
            category: '',
            unit: '',
            search: '',
            sort: 'newest'
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateMeta();
    }

    bindEvents() {
        document.getElementById('am-category').addEventListener('change', (e) => {
            this.filters.category = e.target.value;
            this.refreshMaterials();
        });

        document.getElementById('am-unit').addEventListener('change', (e) => {
            this.filters.unit = e.target.value;
            this.refreshMaterials();
        });

        document.getElementById('am-sort').addEventListener('change', (e) => {
            this.filters.sort = e.target.value;
            this.refreshMaterials();
        });

        const searchInput = document.getElementById('am-search');
        searchInput.addEventListener('input', this.debounce(() => {
            this.filters.search = searchInput.value;
            this.refreshMaterials();
        }, 300));

        document.getElementById('am-search-clear').addEventListener('click', () => {
            searchInput.value = '';
            this.filters.search = '';
            this.refreshMaterials();
        });

        document.getElementById('am-prev').addEventListener('click', () => this.prevPage());
        document.getElementById('am-next').addEventListener('click', () => this.nextPage());

        this.bindModalEvents();
        
        document.addEventListener('click', (e) => {
            const row = e.target.closest('tr[data-id]');
            if (!row) return;

            const materialId = row.dataset.id;
            const action = e.target.closest('[data-action]')?.dataset.action;

            if (action === 'view') this.viewMaterial(materialId);
            if (action === 'edit') this.editMaterial(materialId);
            if (action === 'delete') this.deleteMaterial(materialId);
        });
    }

    bindModalEvents() {
        const overlay = document.getElementById('am-modal-overlay');
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.closest('[data-close]')) {
                this.closeModals();
            }
        });

        document.getElementById('am-open-edit').addEventListener('click', () => {
            if (this.currentMaterialId) {
                this.closeModals();
                this.editMaterial(this.currentMaterialId);
            }
        });

        document.getElementById('am-view-open-lightbox').addEventListener('click', () => {
            this.openPhotosModal(this.currentMaterialId);
        });

        document.getElementById('am-edit-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveMaterial();
        });

        document.getElementById('am-edit-new').addEventListener('change', (e) => {
            this.handleNewImages(e.target.files);
        });

        const scoreInput = document.getElementById('am-edit-recyclability');
        const scoreSlider = document.getElementById('am-edit-recyclability-slider');
        
        scoreInput.addEventListener('input', (e) => {
            scoreSlider.value = e.target.value;
        });
        
        scoreSlider.addEventListener('input', (e) => {
            scoreInput.value = e.target.value;
        });

        document.getElementById('am-delete-confirm').addEventListener('click', () => {
            this.confirmDelete();
        });

        this.bindPhotosModalEvents();
    }

    bindPhotosModalEvents() {
        const photosModal = document.getElementById('am-photos-modal');
        
        photosModal.querySelector('.lb-nav.prev').addEventListener('click', () => {
            this.navigatePhoto(-1);
        });

        photosModal.querySelector('.lb-nav.next').addEventListener('click', () => {
            this.navigatePhoto(1);
        });

        document.addEventListener('keydown', (e) => {
            if (!photosModal.classList.contains('hidden')) {
                if (e.key === 'ArrowLeft') this.navigatePhoto(-1);
                if (e.key === 'ArrowRight') this.navigatePhoto(1);
                if (e.key === 'Escape') this.closeModals();
            }
        });
    }

    async refreshMaterials() {
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                ...this.filters
            });

            const response = await fetch(`/admin/materials/data?${params}`);
            const data = await response.json();

            this.renderMaterials(data.materials);
            this.updatePagination(data);
            this.updateMeta(data.total);
        } catch (error) {
            console.error('Failed to refresh materials:', error);
        }
    }

    renderMaterials(materials) {
        const tbody = document.getElementById('am-tbody');
        
        if (materials.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align:center;padding:2rem;color:#64748b;">
                        No materials found matching your criteria.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = materials.map(material => `
            <tr data-id="${material.id}">
                <td>${material.id}</td>
                <td>${this.escapeHtml(material.name)}</td>
                <td><span class="badge cat-${material.category}">${this.capitalize(material.category)}</span></td>
                <td>${material.quantity}</td>
                <td>${material.unit}</td>
                <td>
                    <div class="score-bar">
                        <div class="score-fill" style="width: ${material.recyclability_score}%"></div>
                        <span class="score-text">${material.recyclability_score}%</span>
                    </div>
                </td>
                <td>${material.maker?.name || '—'}</td>
                <td>${material.products_count || 0}</td>
                <td>${material.created_at}</td>
                <td class="actions">
                    <div class="action-group">
                        <button class="icon-btn view is-blue" data-action="view" data-tooltip="View" aria-label="View material">
                            <i class="fa-solid fa-eye"></i>
                            <span class="sr-only">View</span>
                        </button>
                        <button class="icon-btn edit is-green" data-action="edit" data-tooltip="Edit" aria-label="Edit material">
                            <i class="fa-solid fa-pen"></i>
                            <span class="sr-only">Edit</span>
                        </button>
                        <button class="icon-btn delete is-red" data-action="delete" data-tooltip="Delete" aria-label="Delete material">
                            <i class="fa-solid fa-trash"></i>
                            <span class="sr-only">Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updatePagination(data) {
        const prevBtn = document.getElementById('am-prev');
        const nextBtn = document.getElementById('am-next');
        const pageInfo = document.getElementById('am-pageinfo');

        this.currentPage = data.current_page;
        this.totalPages = data.last_page;

        prevBtn.disabled = this.currentPage === 1;
        nextBtn.disabled = this.currentPage === this.totalPages;

        pageInfo.textContent = `Page ${this.currentPage} of ${this.totalPages}`;
    }

    updateMeta(total = null) {
        const metaEl = document.getElementById('am-meta');
        if (total !== null) {
            metaEl.textContent = `${total} material${total !== 1 ? 's' : ''}`;
        }
    }

    async viewMaterial(id) {
        this.currentMaterialId = id;
        this.showModal('am-view-modal');
        
        try {
            document.getElementById('am-view-loading').classList.remove('hidden');
            document.getElementById('am-view-error').classList.add('hidden');
            document.getElementById('am-view-content').classList.add('hidden');

            const response = await fetch(`/admin/materials/${id}/view`);
            const material = await response.json();

            this.renderMaterialView(material);
        } catch (error) {
            console.error('Failed to load material:', error);
            document.getElementById('am-view-loading').classList.add('hidden');
            document.getElementById('am-view-error').classList.remove('hidden');
        }
    }

    renderMaterialView(material) {
        document.getElementById('am-vh-name').textContent = material.name;
        
        const metaHtml = `
            <span class="am-pill"><i class="fa-solid fa-tag"></i> ${this.capitalize(material.category)}</span>
            <span class="am-pill"><i class="fa-solid fa-weight-hanging"></i> ${material.quantity} ${material.unit}</span>
            <span class="am-pill"><i class="fa-solid fa-recycle"></i> ${material.recyclability_score}%</span>
            ${material.maker ? `<span class="am-pill"><i class="fa-solid fa-user"></i> ${material.maker.name}</span>` : ''}
        `;
        document.getElementById('am-vh-meta').innerHTML = metaHtml;

        this.renderMaterialImages(material.images);

        const detailsHtml = `
            <div><dt>Category</dt><dd>${this.capitalize(material.category)}</dd></div>
            <div><dt>Quantity</dt><dd>${material.quantity} ${material.unit}</dd></div>
            <div><dt>Recyclability Score</dt><dd>${material.recyclability_score}%</dd></div>
            <div><dt>Maker</dt><dd>${material.maker?.name || '—'}</dd></div>
            <div><dt>Waste Item</dt><dd>${material.waste_item?.title || '—'}</dd></div>
            <div><dt>Created</dt><dd>${material.created_at}</dd></div>
            ${material.updated_at ? `<div><dt>Updated</dt><dd>${material.updated_at}</dd></div>` : ''}
        `;
        document.getElementById('am-view-details').innerHTML = detailsHtml;

        const impactHtml = `
            ${material.co2_kg_saved ? `<div><dt>CO₂ Saved</dt><dd>${material.co2_kg_saved} kg</dd></div>` : ''}
            ${material.landfill_kg_avoided ? `<div><dt>Landfill Avoided</dt><dd>${material.landfill_kg_avoided} kg</dd></div>` : ''}
            ${material.energy_saved_kwh ? `<div><dt>Energy Saved</dt><dd>${material.energy_saved_kwh} kWh</dd></div>` : ''}
            ${!material.co2_kg_saved && !material.landfill_kg_avoided ? '<div><dt>Impact Data</dt><dd>Not calculated</dd></div>' : ''}
        `;
        document.getElementById('am-view-impact').innerHTML = impactHtml;

        const productsHtml = material.products && material.products.length > 0 
            ? material.products.map(product => `
                <div class="am-product-item">
                    <strong>${this.escapeHtml(product.name)}</strong>
                    <br>
                    <small>Quantity used: ${product.pivot.quantity_used} ${product.pivot.unit}</small>
                </div>
            `).join('')
            : 'Not used in any products yet';
        document.getElementById('am-view-products').innerHTML = productsHtml;

        document.getElementById('am-view-description').textContent = material.description || 'No description provided.';

        document.getElementById('am-view-loading').classList.add('hidden');
        document.getElementById('am-view-content').classList.remove('hidden');
    }

   renderMaterialImages(images) {
    const container = document.getElementById('am-view-images');
    const countEl = document.getElementById('am-view-img-count');
    const lightboxBtn = document.getElementById('am-view-open-lightbox');

    container.innerHTML = '';
    container.className = 'am-image-strip';

    if (!images || images.length === 0) {
        container.classList.add('no-images');
        countEl.textContent = '0';
        countEl.classList.remove('has-images');
        lightboxBtn.style.display = 'none';
        return;
    }

    if (images.length === 1) {
        container.classList.add('single-image');
    } else if (images.length === 2) {
        container.classList.add('double-image');
    }

    images.forEach((image, index) => {
        const imageItem = document.createElement('div');
        imageItem.className = 'am-image-item';
        imageItem.innerHTML = `
            <img src="${image.thumbnail_url || image.image_url}" 
                 alt="Material image ${index + 1}" 
                 loading="lazy" 
                 onerror="this.src='/images/placeholder-material.png'" />
        `;
        
        imageItem.addEventListener('click', () => {
            this.openPhotosModal(this.currentMaterialId, index);
        });
        
        container.appendChild(imageItem);
    });

    countEl.textContent = images.length;
    countEl.classList.add('has-images');
    lightboxBtn.style.display = 'flex';
}

    async editMaterial(id) {
        this.currentMaterialId = id;
        this.showModal('am-edit-modal');
        
        try {
            const response = await fetch(`/admin/materials/${id}/edit-form`);
            const material = await response.json();

            this.populateEditForm(material);
        } catch (error) {
            console.error('Failed to load material for edit:', error);
            alert('Failed to load material data');
            this.closeModals();
        }
    }

    populateEditForm(material) {
    console.log('Populating form with material:', material);
    
    document.getElementById('am-edit-name').value = material.name || '';
    document.getElementById('am-edit-category').value = material.category || '';
    document.getElementById('am-edit-quantity').value = material.quantity || '';
    document.getElementById('am-edit-unit').value = material.unit || '';
    document.getElementById('am-edit-recyclability').value = material.recyclability_score || '';
    document.getElementById('am-edit-recyclability-slider').value = material.recyclability_score || '';
    document.getElementById('am-edit-maker').value = material.maker_id || '';
    document.getElementById('am-edit-description').value = material.description || '';

    console.log('Form values after population:');
    console.log('Name:', document.getElementById('am-edit-name').value);
    console.log('Category:', document.getElementById('am-edit-category').value);
    console.log('Quantity:', document.getElementById('am-edit-quantity').value);
    console.log('Unit:', document.getElementById('am-edit-unit').value);

    this.renderEditImages(material.images);
}

    renderEditImages(images) {
        const container = document.getElementById('am-edit-existing');
        
        if (!images || images.length === 0) {
            container.innerHTML = '<div style="color:#64748b;font-size:.8rem;">No images</div>';
            return;
        }

        container.innerHTML = images.map(image => `
            <div class="am-existing-image" data-id="${image.id}">
                <img src="${image.thumbnail_url || image.image_url}" alt="Material image" />
                <button type="button" class="am-remove-image" title="Remove image">&times;</button>
            </div>
        `).join('');

        container.querySelectorAll('.am-remove-image').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const imageEl = e.target.closest('.am-existing-image');
                imageEl.remove();
                
                const removeInput = document.getElementById('am-edit-remove');
                const currentRemoved = removeInput.value ? removeInput.value.split(',') : [];
                currentRemoved.push(imageEl.dataset.id);
                removeInput.value = currentRemoved.join(',');
            });
        });
    }

    handleNewImages(files) {
        const previewsContainer = document.getElementById('am-edit-new-previews');
        
        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.createElement('div');
                preview.className = 'am-new-preview';
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" />
                    <button type="button" class="am-remove-preview" title="Remove image">&times;</button>
                `;
                
                previewsContainer.appendChild(preview);

                preview.querySelector('.am-remove-preview').addEventListener('click', () => {
                    preview.remove();
                });
            };
            reader.readAsDataURL(file);
        });
    }

async saveMaterial() {
    const form = document.getElementById('am-edit-form');
    const formData = new FormData(form);

    const keepImages = Array.from(document.querySelectorAll('.am-existing-image'))
        .map(el => el.dataset.id)
        .join(',');
    formData.set('keep_images', keepImages);

    formData.append('_method', 'PUT');

    console.log('=== FORMDATA CONTENTS ===');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }

    try {
        const response = await fetch(`/admin/materials/${this.currentMaterialId}/admin-update`, {
            method: 'POST', 
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            const result = await response.json();
            this.closeModals();
            this.refreshMaterials();
        } else {
            const errorData = await response.json();
            console.error('Server error response:', errorData);
            throw new Error(errorData.message || 'Failed to update material');
        }
    } catch (error) {
        console.error('Failed to save material:', error);
    }
}
    deleteMaterial(id) {
        this.currentMaterialId = id;
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const name = row?.querySelector('td:nth-child(2)')?.textContent || 'this material';
        
        document.getElementById('am-delete-text').textContent = 
            `Are you sure you want to delete "${name}"? This action cannot be undone.`;
        
        this.showModal('am-delete-modal');
    }

    async confirmDelete() {
        try {
            const response = await fetch(`/admin/materials/${this.currentMaterialId}/admin-delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.closeModals();
                this.refreshMaterials();
            } else {
                throw new Error('Failed to delete material');
            }
        } catch (error) {
            console.error('Failed to delete material:', error);
            this.showNotification('Failed to delete material', 'error');
        }
    }

    async openPhotosModal(materialId, startIndex = 0) {
        this.currentPhotoIndex = startIndex;
        this.showModal('am-photos-modal');

        try {
            document.getElementById('am-photos-loader').classList.remove('hidden');
            document.getElementById('am-photos-error').classList.add('hidden');
            document.getElementById('am-photos-main-wrap').classList.add('hidden');

            const response = await fetch(`/materials/${materialId}/images`);
            const images = await response.json();

            this.renderPhotos(images);
        } catch (error) {
            console.error('Failed to load photos:', error);
            document.getElementById('am-photos-loader').classList.add('hidden');
            document.getElementById('am-photos-error').classList.remove('hidden');
        }
    }

    renderPhotos(images) {
        if (!images || images.length === 0) {
            document.getElementById('am-photos-loader').classList.add('hidden');
            document.getElementById('am-photos-error').classList.remove('hidden');
            document.getElementById('am-photos-error').innerHTML = 
                '<p style="font-size:.75rem;color:var(--wi-gray-700);"><i class="fa-solid fa-image" style="margin-right:.35rem;"></i><span>No photos available</span></p>';
            return;
        }

        this.photos = images;
        this.showPhoto(this.currentPhotoIndex);

        const thumbsContainer = document.getElementById('am-photos-thumbs');
        thumbsContainer.innerHTML = images.map((image, index) => `
            <img src="${image.thumbnail_url || image.image_url}" 
                 alt="Thumbnail ${index + 1}"
                 class="lb-thumb ${index === this.currentPhotoIndex ? 'active' : ''}"
                 data-index="${index}"
                 style="width:60px;height:45px;object-fit:cover;border-radius:4px;cursor:pointer;border:2px solid ${index === this.currentPhotoIndex ? '#3b82f6' : 'transparent'}" />
        `).join('');

        thumbsContainer.querySelectorAll('.lb-thumb').forEach(thumb => {
            thumb.addEventListener('click', () => {
                this.showPhoto(parseInt(thumb.dataset.index));
            });
        });

        document.getElementById('am-photos-loader').classList.add('hidden');
        document.getElementById('am-photos-main-wrap').classList.remove('hidden');
    }

    showPhoto(index) {
        if (!this.photos || this.photos.length === 0) return;

        this.currentPhotoIndex = (index + this.photos.length) % this.photos.length;
        const photo = this.photos[this.currentPhotoIndex];

        const mainImage = document.getElementById('am-photos-main-image');
        mainImage.src = photo.image_url;
        mainImage.classList.remove('hidden');

        document.getElementById('am-photos-caption').textContent = 
            `Image ${this.currentPhotoIndex + 1} of ${this.photos.length}`;

        document.querySelectorAll('.lb-thumb').forEach((thumb, i) => {
            thumb.style.borderColor = i === this.currentPhotoIndex ? '#3b82f6' : 'transparent';
        });
    }

    navigatePhoto(direction) {
        this.showPhoto(this.currentPhotoIndex + direction);
    }

    showModal(modalId) {
        document.getElementById('am-modal-overlay').classList.remove('hidden');
        document.getElementById(modalId).classList.remove('hidden');
    }

    closeModals() {
        document.getElementById('am-modal-overlay').classList.add('hidden');
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('hidden');
        });
        
        document.getElementById('am-edit-new-previews').innerHTML = '';
        document.getElementById('am-edit-new').value = '';
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.refreshMaterials();
        }
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.refreshMaterials();
        }
    }

    showNotification(message, type = 'info') {
        alert(message);
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AdminMaterials();
});