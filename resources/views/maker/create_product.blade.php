@extends('layouts.app')

@push('head')
@vite(['resources/css/create-product.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-form-container">
        <h1 style="margin-bottom: 1.5rem; color: #333;">Create New Product</h1>
        <form action="{{ route('maker.products.store') }}" method="POST" enctype="multipart/form-data" class="product-form" novalidate>
            @csrf
            <div class="form-group full-width">
                <label for="name">Product Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                @error('name')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full-width">
                <label for="description">Product Description *</label>
                <textarea name="description" id="description" rows="4" required>{{ old('description') }}</textarea>
                @error('description')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            {{-- Multiple Materials Selection --}}
            <div class="form-group full-width">
                <label for="materials-search">Select Materials *</label>
                <div class="materials-search-container">
                    <input type="text" id="materials-search" placeholder="Search materials..." class="materials-search">
                    <div id="materials-dropdown" class="materials-dropdown">
                        @foreach($materials as $material)
                            <div class="material-option" 
                                 data-id="{{ $material->id }}"
                                 data-quantity="{{ $material->quantity }}"
                                 data-unit="{{ $material->unit }}"
                                 data-name="{{ $material->name }}">
                                <span class="material-name">{{ $material->name }}</span>
                                <span class="material-details">{{ $material->quantity }} {{ strtoupper($material->unit) }} available</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('materials')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            {{-- Selected Materials Table --}}
            <div class="form-group full-width">
                <label>Selected Materials</label>
                <div id="selected-materials-container" class="selected-materials-container">
                    <div class="materials-table-header">
                        <span>Material</span>
                        <span>Available</span>
                        <span>Quantity Used</span>
                        <span>Action</span>
                    </div>
                    <div id="selected-materials-list">
                        <!-- Selected materials will appear here -->
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="price">Price (€) *</label>
                <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" required>
                @error('price')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', 1) }}" required>
                @error('stock')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="sku">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="Auto-generated if empty">
                <span class="helper-text">Leave empty for auto-generation</span>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" name="weight" id="weight" step="0.1" min="0" value="{{ old('weight') }}">
            </div>

            <div class="form-group">
                <label for="warranty_months">Warranty (months)</label>
                <input type="number" name="warranty_months" id="warranty_months" min="0" value="{{ old('warranty_months') }}">
            </div>

            <div class="form-group full-width">
                <label for="care_instructions">Care Instructions</label>
                <textarea name="care_instructions" id="care_instructions" rows="3">{{ old('care_instructions') }}</textarea>
            </div>

            <div class="form-group full-width">
                <label for="images">Product Images *</label>
                <input type="file" name="images[]" id="images" multiple accept="image/*" required>
                <span class="helper-text">Select multiple images (PNG, JPG, JPEG up to 5MB each)</span>
                @error('images')<span class="error-text">{{ $message }}</span>@enderror
                @error('images.*')<span class="error-text">{{ $message }}</span>@enderror
                <div id="imagePreview" class="image-preview-container"></div>
            </div>

            <div class="form-group full-width" style="grid-column: 1 / -1; margin-top: 1rem;">
                <button type="submit" class="btn-primary">Create Product</button>
            </div>
        </form>
    </div>

    <div class="instructions-sidebar">
        <div class="instructions-panel">
            <div class="instructions-header">
                <h3>Creating a Product</h3>
            </div>
            <div class="instructions-content">
                <ul class="instructions-list">
                    <li><strong>Name & Description:</strong> Be clear and descriptive about your product</li>
                    <li><strong>Source Materials:</strong> Select one or more materials from your inventory</li>
                    <li><strong>Quantity Used:</strong> Specify how much of each material you'll use</li>
                    <li><strong>Pricing:</strong> Consider material costs and your time</li>
                    <li><strong>Images:</strong> Show multiple angles and details</li>
                    <li><strong>Stock:</strong> Set realistic available quantities</li>
                </ul>
                <div style="background: #e8f5e8; padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                    <strong>Tip:</strong> Highlight the sustainability story of your product in the description.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialsSearch = document.getElementById('materials-search');
    const materialsDropdown = document.getElementById('materials-dropdown');
    const selectedMaterialsList = document.getElementById('selected-materials-list');
    const selectedMaterials = new Set();

    // Image preview functionality
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('imagePreview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            imagePreview.innerHTML = '';
            
            if (this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'image-preview-wrapper';
                            wrapper.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="image-preview">
                                <button type="button" class="remove-image" onclick="this.parentElement.remove()">
                                    ×
                                </button>
                            `;
                            imagePreview.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    // Materials search and selection functionality
    if (materialsSearch && materialsDropdown) {
        // Show dropdown on focus
        materialsSearch.addEventListener('focus', function() {
            materialsDropdown.style.display = 'block';
            filterMaterials();
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!materialsSearch.contains(e.target) && !materialsDropdown.contains(e.target)) {
                materialsDropdown.style.display = 'none';
            }
        });

        // Filter materials on search
        materialsSearch.addEventListener('input', filterMaterials);

        // Handle material selection
        materialsDropdown.addEventListener('click', function(e) {
            const materialOption = e.target.closest('.material-option');
            if (materialOption) {
                const materialId = materialOption.dataset.id;
                
                if (!selectedMaterials.has(materialId)) {
                    addMaterialRow(materialOption);
                    selectedMaterials.add(materialId);
                }
                
                // Clear search and hide dropdown
                materialsSearch.value = '';
                materialsDropdown.style.display = 'none';
            }
        });

        function filterMaterials() {
            const searchTerm = materialsSearch.value.toLowerCase();
            const options = materialsDropdown.querySelectorAll('.material-option');
            
            options.forEach(option => {
                const materialName = option.querySelector('.material-name').textContent.toLowerCase();
                if (materialName.includes(searchTerm)) {
                    option.style.display = 'flex';
                } else {
                    option.style.display = 'none';
                }
            });
        }
    }

    function addMaterialRow(materialOption) {
        const materialId = materialOption.dataset.id;
        const materialName = materialOption.dataset.name;
        const availableQuantity = materialOption.dataset.quantity;
        const unit = materialOption.dataset.unit;

        const row = document.createElement('div');
        row.className = 'material-row';
        row.innerHTML = `
            <input type="hidden" name="materials[${materialId}][id]" value="${materialId}">
            <span class="material-name">${materialName}</span>
            <span class="material-available">${availableQuantity} ${unit}</span>
            <input type="number" 
                   name="materials[${materialId}][quantity_used]" 
                   min="0.01" 
                   max="${availableQuantity}"
                   step="0.01" 
                   value=""
                   placeholder="0.00"
                   required
                   class="quantity-input">
            <button type="button" class="remove-material" onclick="removeMaterial('${materialId}', this)">
                <i class="fa-solid fa-trash"></i>
            </button>
        `;

        selectedMaterialsList.appendChild(row);
    }

    window.removeMaterial = function(materialId, button) {
        selectedMaterials.delete(materialId);
        button.closest('.material-row').remove();
    };
});
</script>
@endpush