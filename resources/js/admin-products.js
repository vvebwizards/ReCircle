class AdminProducts {
    constructor() {
        this.currentPage = 1;
        this.totalPages = 1;
        this.currentProductId = null;
        this.filters = {
            status: '',
            maker: '',
            search: '',
            sort: 'newest'
        };
        
        this.init();
    }

    init() {
        console.log('AdminProducts initialized');
        this.bindEvents();
        this.updateMeta();
    }

    bindEvents() {
        console.log('Binding events...');
        
        const statusFilter = document.getElementById('ap-status');
        const makerFilter = document.getElementById('ap-maker');
        const sortFilter = document.getElementById('ap-sort');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filters.status = e.target.value;
                this.refreshProducts();
            });
        }
        
        if (makerFilter) {
            makerFilter.addEventListener('change', (e) => {
                this.filters.maker = e.target.value;
                this.refreshProducts();
            });
        }
        
        if (sortFilter) {
            sortFilter.addEventListener('change', (e) => {
                this.filters.sort = e.target.value;
                this.refreshProducts();
            });
        }

        const searchInput = document.getElementById('ap-search');
        const searchClear = document.getElementById('ap-search-clear');
        
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(() => {
                this.filters.search = searchInput.value;
                this.refreshProducts();
            }, 300));
        }
        
        if (searchClear) {
            searchClear.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                this.filters.search = '';
                this.refreshProducts();
            });
        }

        const prevBtn = document.getElementById('ap-prev');
        const nextBtn = document.getElementById('ap-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prevPage());
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextPage());
        }

        this.bindModalEvents();
        
        document.addEventListener('click', (e) => {
            console.log('Click event:', e.target);
            
            const actionBtn = e.target.closest('[data-action]');
            if (!actionBtn) return;
            
            const row = actionBtn.closest('tr[data-id]');
            if (!row) return;

            const productId = row.dataset.id;
            const action = actionBtn.dataset.action;
            
            console.log(`Action: ${action}, Product ID: ${productId}`);

            if (action === 'view') {
                this.viewProduct(productId);
            } else if (action === 'edit') {
                this.editProduct(productId);
            } else if (action === 'delete') {
                this.deleteProduct(productId);
            }
        });
    }

    bindModalEvents() {
        const overlay = document.getElementById('ap-modal-overlay');
        if (!overlay) {
            console.error('Modal overlay not found!');
            return;
        }
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.closest('[data-close]')) {
                this.closeModals();
            }
        });

        const deleteConfirm = document.getElementById('ap-delete-confirm');
        if (deleteConfirm) {
            deleteConfirm.addEventListener('click', () => {
                this.confirmDelete();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModals();
            }
        });
    }

    async refreshProducts() {
        try {
            console.log('Refreshing products with filters:', this.filters);
            
            const params = new URLSearchParams({
                page: this.currentPage,
                ...this.filters
            });

            const response = await fetch(`/admin/products/data?${params}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Products data received:', data);

            this.renderProducts(data.products);
            this.updatePagination(data);
            this.updateMeta(data.total);
        } catch (error) {
            console.error('Failed to refresh products:', error);
            this.showNotification('Failed to load products', 'error');
        }
    }

    renderProducts(products) {
        const tbody = document.getElementById('ap-tbody');
        if (!tbody) {
            console.error('Products table body not found!');
            return;
        }
        
        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" style="text-align:center;padding:2rem;color:#64748b;">
                        No products found matching your criteria.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = products.map(product => `
            <tr data-id="${product.id}">
                <td>${product.id}</td>
                <td>${this.escapeHtml(product.sku)}</td>
                <td>${this.escapeHtml(product.name)}</td>
                <td><span class="badge status-${product.status}">${this.capitalize(product.status)}</span></td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.stock}</td>
                <td>${product.maker?.name || '—'}</td>
                <td>${product.materials_count || 0}</td>
                <td>${product.created_at}</td>
                <td class="actions">
                    <div class="action-group">
                        <button class="icon-btn view is-blue" data-action="view" data-tooltip="View" aria-label="View product">
                            <i class="fa-solid fa-eye"></i>
                            <span class="sr-only">View</span>
                        </button>
                        <button class="icon-btn edit is-green" data-action="edit" data-tooltip="Edit" aria-label="Edit product">
                            <i class="fa-solid fa-pen"></i>
                            <span class="sr-only">Edit</span>
                        </button>
                        <button class="icon-btn delete is-red" data-action="delete" data-tooltip="Delete" aria-label="Delete product">
                            <i class="fa-solid fa-trash"></i>
                            <span class="sr-only">Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        console.log(`Rendered ${products.length} products`);
    }

    updatePagination(data) {
        const prevBtn = document.getElementById('ap-prev');
        const nextBtn = document.getElementById('ap-next');
        const pageInfo = document.getElementById('ap-pageinfo');

        this.currentPage = data.current_page;
        this.totalPages = data.last_page;

        if (prevBtn) prevBtn.disabled = this.currentPage === 1;
        if (nextBtn) nextBtn.disabled = this.currentPage === this.totalPages;
        if (pageInfo) pageInfo.textContent = `Page ${this.currentPage} of ${this.totalPages}`;
    }

    updateMeta(total = null) {
        const metaEl = document.getElementById('ap-meta');
        if (metaEl && total !== null) {
            metaEl.textContent = `${total} product${total !== 1 ? 's' : ''}`;
        }
    }

    async viewProduct(id) {
        console.log('Viewing product:', id);
        this.currentProductId = id;
        this.showModal('ap-view-modal');
        
        try {
            this.showViewLoading();
            
            const response = await fetch(`/admin/products/${id}/view`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const product = await response.json();
            console.log('Product data for view:', product);
            
            this.renderProductView(product);
            
        } catch (error) {
            console.error('Failed to load product:', error);
            this.showViewError();
        }
    }

    showViewLoading() {
        const viewBody = document.getElementById('ap-view-body');
        if (!viewBody) return;
        
        viewBody.innerHTML = `
            <div id="ap-view-loading" class="modal-skeleton">
                <div class="sk-header-line" style="width:55%;height:14px;"></div>
                <div class="sk-pills" style="display:flex;gap:.4rem;margin:.6rem 0 1rem;">
                    <div class="sk-pill" style="width:70px;height:20px;"></div>
                    <div class="sk-pill" style="width:55px;height:20px;"></div>
                    <div class="sk-pill" style="width:40px;height:20px;"></div>
                </div>
                <div class="sk-panels" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.9rem;">
                    <div class="sk-box" style="height:140px;"></div>
                    <div class="sk-box" style="height:140px;"></div>
                </div>
            </div>
        `;
    }

    showViewError() {
        const viewBody = document.getElementById('ap-view-body');
        if (!viewBody) return;
        
        viewBody.innerHTML = `
            <div id="ap-view-error" style="text-align:center;padding:2rem 1rem;">
                <p style="margin:0 0 .75rem;font-size:.8rem;color:var(--wi-gray-700);">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:.4rem;"></i>
                    Failed to load product.
                </p>
                <button type="button" class="btn btn-secondary" data-close>Close</button>
            </div>
        `;
    }

    renderProductView(product) {
        const viewBody = document.getElementById('ap-view-body');
        if (!viewBody) return;
        
        const materialsHtml = product.materials && product.materials.length > 0 
            ? product.materials.map(material => `
                <div class="ap-material-item">
                    <strong>${this.escapeHtml(material.name)}</strong> 
                    (${this.capitalize(material.category)})
                    <br>
                    <small>Quantity: ${material.quantity_used} ${material.unit}</small>
                </div>
            `).join('')
            : 'No materials used';

        const imagesHtml = product.images && product.images.length > 0
            ? product.images.map(image => `
                <div class="am-image-item">
                    <img src="${image.thumbnail_url || image.image_url}" alt="Product image" />
                </div>
            `).join('')
            : '<div style="color:#64748b;font-size:.8rem;">No images</div>';

        viewBody.innerHTML = `
            <div id="ap-view-content" class="ap-view-wrap">
                <div class="ap-view-head">
                    <h2 id="ap-vh-name" class="ap-view-title">${this.escapeHtml(product.name)}</h2>
                    <div id="ap-vh-meta" class="ap-pill-row">
                        <span class="ap-pill status-${product.status}">
                            <i class="fa-solid fa-circle"></i> ${this.capitalize(product.status)}
                        </span>
                        <span class="ap-pill">
                            <i class="fa-solid fa-tag"></i> ${this.escapeHtml(product.sku)}
                        </span>
                        <span class="ap-pill">
                            <i class="fa-solid fa-dollar-sign"></i> $${parseFloat(product.price).toFixed(2)}
                        </span>
                    </div>
                </div>
                
                <div class="am-section am-images-block">
                    <div class="am-section-head">
                        <span class="am-icon-badge"><i class="fa-solid fa-image"></i></span>
                        <span class="am-section-label">Images</span>
                        <div class="am-section-spacer"></div>
                        <span id="am-view-img-count" class="am-count-pill">${product.images?.length || 0}</span>
                    </div>
                    <div id="am-view-images" class="am-image-strip">
                        ${imagesHtml}
                    </div>
                </div>

                <div class="ap-panels-grid">
                    <div class="ap-panel">
                        <div class="ap-panel-head">
                            <span class="ap-icon-badge"><i class="fa-solid fa-circle-info"></i></span>
                            <span class="ap-panel-title">Details</span>
                        </div>
                        <dl id="ap-view-details" class="ap-dl">
                            <div><dt>SKU</dt><dd>${this.escapeHtml(product.sku)}</dd></div>
                            <div><dt>Status</dt><dd>${this.capitalize(product.status)}</dd></div>
                            <div><dt>Price</dt><dd>$${parseFloat(product.price).toFixed(2)}</dd></div>
                            <div><dt>Stock</dt><dd>${product.stock}</dd></div>
                            <div><dt>Maker</dt><dd>${product.maker?.name || '—'}</dd></div>
                            <div><dt>Created</dt><dd>${product.created_at}</dd></div>
                            ${product.updated_at ? `<div><dt>Updated</dt><dd>${product.updated_at}</dd></div>` : ''}
                        </dl>
                    </div>
                    
                    <div class="ap-panel">
                        <div class="ap-panel-head">
                            <span class="ap-icon-badge"><i class="fa-solid fa-cube"></i></span>
                            <span class="ap-panel-title">Materials Used</span>
                        </div>
                        <div id="ap-view-materials" class="ap-materials-list">
                            ${materialsHtml}
                        </div>
                    </div>
                    
                    <div class="ap-panel">
                        <div class="ap-panel-head">
                            <span class="ap-icon-badge"><i class="fa-solid fa-note-sticky"></i></span>
                            <span class="ap-panel-title">Description</span>
                        </div>
                        <div id="ap-view-description" class="am-notes">
                            ${product.description || 'No description provided.'}
                        </div>
                    </div>
                </div>
                
                <div class="ap-actions-row">
                    <button class="ap-btn ghost" data-close type="button">Close</button>
                    <button class="ap-btn primary" id="ap-open-edit" type="button">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit</span>
                    </button>
                </div>
            </div>
        `;

        const editBtn = document.getElementById('ap-open-edit');
        if (editBtn) {
            editBtn.addEventListener('click', () => {
                this.closeModals();
                this.editProduct(this.currentProductId);
            });
        }
    }

  async editProduct(id) {
        console.log('Editing product:', id);
        this.currentProductId = id;
        this.showModal('ap-edit-modal');
        
        try {
            // Show loading state for edit modal
            this.showEditLoading();
            
            const response = await fetch(`/admin/products/${id}/edit-form`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const product = await response.json();
            console.log('Product data for edit:', product);
            
            this.renderEditForm(product);
            
        } catch (error) {
            console.error('Failed to load product for edit:', error);
            this.showNotification('Failed to load product data for editing', 'error');
            this.closeModals();
        }
    }

    showEditLoading() {
        const editBody = document.querySelector('.ap-edit-body');
        if (!editBody) return;
        
        editBody.innerHTML = `
            <div id="ap-edit-loading" class="modal-skeleton">
                <div class="sk-header-line" style="width:55%;height:14px;"></div>
                <div class="sk-pills" style="display:flex;gap:.4rem;margin:.6rem 0 1rem;">
                    <div class="sk-pill" style="width:70px;height:20px;"></div>
                    <div class="sk-pill" style="width:55px;height:20px;"></div>
                    <div class="sk-pill" style="width:40px;height:20px;"></div>
                </div>
                <div class="sk-panels" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.9rem;">
                    <div class="sk-box" style="height:140px;"></div>
                    <div class="sk-box" style="height:140px;"></div>
                </div>
            </div>
        `;
    }

    renderEditForm(product) {
        const editBody = document.querySelector('.ap-edit-body');
        if (!editBody) return;
        
        editBody.innerHTML = `
            <form id="ap-edit-form" class="ap-edit-form">
                <input type="hidden" name="keep_images" id="ap-edit-keep" />
                <input type="hidden" name="remove_images" id="ap-edit-remove" />
                
                <div class="ap-edit-grid">
                    <div class="ap-field">
                        <label for="ap-edit-name" class="ap-field-label">Name *</label>
                        <input type="text" name="name" id="ap-edit-name" required class="ap-input" value="${this.escapeHtml(product.name || '')}" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-status" class="ap-field-label">Status *</label>
                        <select name="status" id="ap-edit-status" required class="ap-input">
                            <option value="draft" ${product.status === 'draft' ? 'selected' : ''}>Draft</option>
                            <option value="published" ${product.status === 'published' ? 'selected' : ''}>Published</option>
                            <option value="archived" ${product.status === 'archived' ? 'selected' : ''}>Archived</option>
                        </select>
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-price" class="ap-field-label">Price *</label>
                        <input type="number" step="0.01" min="0" name="price" id="ap-edit-price" required class="ap-input" value="${product.price || ''}" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-stock" class="ap-field-label">Stock *</label>
                        <input type="number" min="0" name="stock" id="ap-edit-stock" required class="ap-input" value="${product.stock || ''}" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-warranty" class="ap-field-label">Warranty (months)</label>
                        <input type="number" min="0" name="warranty_months" id="ap-edit-warranty" class="ap-input" value="${product.warranty_months || ''}" />
                    </div>
                    
                    
                    <div class="ap-field ap-description-field">
                        <label for="ap-edit-description" class="ap-field-label">Description</label>
                        <textarea name="description" id="ap-edit-description" rows="4" class="ap-input ap-textarea">${this.escapeHtml(product.description || '')}</textarea>
                    </div>
                    
                    <div class="ap-field ap-full-width">
                        <label for="ap-edit-care-instructions" class="ap-field-label">Care Instructions</label>
                        <textarea name="care_instructions" id="ap-edit-care-instructions" rows="3" class="ap-input ap-textarea">${this.escapeHtml(product.care_instructions || '')}</textarea>
                    </div>
                </div>
                
                <div class="ap-edit-images">
                    <div class="am-section-head" style="margin-bottom:.55rem;">
                        <span class="am-icon-badge"><i class="fa-solid fa-images"></i></span>
                        <span class="am-section-label">Images</span>
                    </div>
                    <div id="ap-edit-existing" class="am-edit-existing">
                        ${this.renderEditImages(product.images)}
                    </div>
                    <div class="am-edit-upload-row">
                        <label class="upload-tile am-upload-tile">
                            <input type="file" name="new_images[]" id="ap-edit-new" multiple hidden accept="image/*" />
                            <span><i class="fa-solid fa-upload"></i> Add Images</span>
                        </label>
                        <div id="ap-edit-new-previews" class="am-edit-new-previews"></div>
                    </div>
                </div>
                
                <div class="ap-actions-row">
                    <button type="button" class="ap-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="ap-btn primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        `;

        const form = document.getElementById('ap-edit-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProduct();
            });
        }

        this.bindImageUploadEvents();
    }

    renderEditImages(images) {
        if (!images || images.length === 0) {
            return '<div style="color:#64748b;font-size:.8rem;">No images</div>';
        }

        return images.map(image => `
            <div class="am-existing-image" data-id="${image.id}">
                <img src="${image.thumbnail_url || image.image_url}" alt="Product image" />
                <button type="button" class="am-remove-image" title="Remove image">&times;</button>
            </div>
        `).join('');
    }

    bindImageUploadEvents() {
        document.querySelectorAll('.am-remove-image').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const imageEl = e.target.closest('.am-existing-image');
                const imageId = imageEl.dataset.id;
                imageEl.remove();
                
                const removeInput = document.getElementById('ap-edit-remove');
                const currentRemoved = removeInput.value ? removeInput.value.split(',') : [];
                currentRemoved.push(imageId);
                removeInput.value = currentRemoved.join(',');
            });
        });

        const fileInput = document.getElementById('ap-edit-new');
        const previewsContainer = document.getElementById('ap-edit-new-previews');
        
        if (fileInput && previewsContainer) {
            fileInput.addEventListener('change', (e) => {
                Array.from(e.target.files).forEach(file => {
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
            });
        }
    }

   async saveProduct() {
    const form = document.getElementById('ap-edit-form');
    if (!form) {
        console.error('Edit form not found!');
        return;
    }

    const formData = new FormData(form);

    const keepImages = Array.from(document.querySelectorAll('.am-existing-image'))
        .map(el => el.dataset.id)
        .join(',');
    formData.set('keep_images', keepImages);

    formData.append('_method', 'PUT');

    console.log('Saving product with data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }

    try {
        const url = `/admin/products/${this.currentProductId}`;
        console.log('Making request to:', url);

        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        // Debug: See what the server actually returns
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        const responseText = await response.text();
        console.log('Raw server response:', responseText.substring(0, 2000)); 

        // Try to parse as JSON, if it fails, we know it's HTML
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Server returned HTML instead of JSON. This indicates a server error.');
            console.error('Full response:', responseText);
            
            // Extract error message from HTML if possible
            const errorMatch = responseText.match(/<title>(.*?)<\/title>/i) || 
                              responseText.match(/<h1>(.*?)<\/h1>/i) ||
                              responseText.match(/<p class?="?message"?>(.*?)<\/p>/i);
            
            const errorMessage = errorMatch ? errorMatch[1] : 'Server returned an error page';
            throw new Error(`Server error (${response.status}): ${errorMessage}`);
        }

        if (response.ok && result.success) {
            this.closeModals();
            this.refreshProducts();
        } else {
            if (result.errors) {
                const errorMessages = Object.values(result.errors).flat().join(', ');
                throw new Error(`Validation failed: ${errorMessages}`);
            }
            throw new Error(result.message || 'Failed to update product');
        }

    } catch (error) {
        console.error('Failed to save product:', error);
        this.showNotification('Failed to update product: ' + error.message, 'error');
    }
}

    populateEditForm(product) {
        const editForm = document.getElementById('ap-edit-form');
        if (!editForm) {
            this.createEditForm();
        }

        document.getElementById('ap-edit-name').value = product.name || '';
        document.getElementById('ap-edit-description').value = product.description || '';
        document.getElementById('ap-edit-status').value = product.status || 'draft';
        document.getElementById('ap-edit-price').value = product.price || '';
        document.getElementById('ap-edit-stock').value = product.stock || '';
        document.getElementById('ap-edit-care-instructions').value = product.care_instructions || '';
        document.getElementById('ap-edit-warranty').value = product.warranty_months || '';
        
        console.log('Form populated with product data');
    }

    createEditForm() {
        const editBody = document.querySelector('.ap-edit-body');
        if (!editBody) return;

        editBody.innerHTML = `
            <form id="ap-edit-form" enctype="multipart/form-data" class="ap-edit-form">
                <input type="hidden" name="keep_images" id="ap-edit-keep" />
                <input type="hidden" name="remove_images" id="ap-edit-remove" />
                
                <div class="ap-edit-grid">
                    <div class="ap-field">
                        <label for="ap-edit-name" class="ap-field-label">Name *</label>
                        <input type="text" name="name" id="ap-edit-name" required class="ap-input" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-status" class="ap-field-label">Status *</label>
                        <select name="status" id="ap-edit-status" required class="ap-input">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-price" class="ap-field-label">Price *</label>
                        <input type="number" step="0.01" min="0" name="price" id="ap-edit-price" required class="ap-input" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-stock" class="ap-field-label">Stock *</label>
                        <input type="number" min="0" name="stock" id="ap-edit-stock" required class="ap-input" />
                    </div>
                    
                    <div class="ap-field">
                        <label for="ap-edit-warranty" class="ap-field-label">Warranty (months)</label>
                        <input type="number" min="0" name="warranty_months" id="ap-edit-warranty" class="ap-input" />
                    </div>
                    
                    
                    <div class="ap-field ap-description-field">
                        <label for="ap-edit-description" class="ap-field-label">Description</label>
                        <textarea name="description" id="ap-edit-description" rows="4" class="ap-input ap-textarea"></textarea>
                    </div>
                    
                    <div class="ap-field ap-full-width">
                        <label for="ap-edit-care-instructions" class="ap-field-label">Care Instructions</label>
                        <textarea name="care_instructions" id="ap-edit-care-instructions" rows="3" class="ap-input ap-textarea"></textarea>
                    </div>
                </div>
                
                <div class="ap-actions-row">
                    <button type="button" class="ap-btn ghost" data-close>Cancel</button>
                    <button type="submit" class="ap-btn primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        `;

        const form = document.getElementById('ap-edit-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProduct();
            });
        }
    }

    deleteProduct(id) {
        console.log('Deleting product:', id);
        this.currentProductId = id;
        
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const name = row?.querySelector('td:nth-child(3)')?.textContent || 'this product';
        
        document.getElementById('ap-delete-text').textContent = 
            `Are you sure you want to delete "${name}"? This action cannot be undone.`;
        
        this.showModal('ap-delete-modal');
    }

    async confirmDelete() {
        if (!this.currentProductId) {
            console.error('No product ID set for deletion');
            return;
        }
        
        try {
            console.log('Confirming deletion for product:', this.currentProductId);
            
            const response = await fetch(`/admin/products/${this.currentProductId}/admin-delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.closeModals();
                this.refreshProducts();
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete product');
            }
        } catch (error) {
            console.error('Failed to delete product:', error);
            this.showNotification('Failed to delete product: ' + error.message, 'error');
        }
    }

    showModal(modalId) {
        const overlay = document.getElementById('ap-modal-overlay');
        const modal = document.getElementById(modalId);
        
        if (overlay && modal) {
            overlay.classList.remove('hidden');
            modal.classList.remove('hidden');
            console.log(`Showing modal: ${modalId}`);
        } else {
            console.error(`Modal or overlay not found: ${modalId}`);
        }
    }

    closeModals() {
        const overlay = document.getElementById('ap-modal-overlay');
        const modals = document.querySelectorAll('.modal');
        
        if (overlay) overlay.classList.add('hidden');
        modals.forEach(modal => modal.classList.add('hidden'));
        console.log('All modals closed');
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.refreshProducts();
        }
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.refreshProducts();
        }
    }

    showNotification(message, type = 'info') {
        console.log(`[${type.toUpperCase()}] ${message}`);
        
        if (type === 'error') {
            alert(`Error: ${message}`);
        } else if (type === 'success') {
            alert(`Success: ${message}`);
        }
    }

    escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    capitalize(str) {
        if (!str) return '';
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
    console.log('DOM loaded, initializing AdminProducts...');
    new AdminProducts();
});

window.AdminProducts = AdminProducts;