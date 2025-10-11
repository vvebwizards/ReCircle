@extends('layouts.app')
@push('head')
@vite(['resources/css/create-product.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-form-container">
        <h1 style="margin-bottom: 1.5rem; color: #333;">Create New Material</h1>
        <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data" class="product-form" novalidate>
            @csrf
            
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <div class="form-group full-width">
                <label for="name">Material Name *</label>
                <input type="text" name="name" id="name" 
                       value="{{ old('name') }}" 
                       placeholder="e.g., Recycled Plastic Pellets" 
                       required>
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category" required>
                    <option value="">Select a category</option>
                    @foreach(\App\Models\Material::CATEGORIES as $category)
                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="unit">Unit *</label>
                <select name="unit" id="unit" required>
                    <option value="">Select unit</option>
                    @foreach(\App\Models\Material::UNITS as $unit)
                        <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>
                            {{ strtoupper($unit) }}
                        </option>
                    @endforeach
                </select>
                @error('unit')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" name="quantity" id="quantity" 
                       value="{{ old('quantity') }}" 
                       placeholder="0.00" step="0.01" min="0" required>
                @error('quantity')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="recyclability_score">Recyclability Score (%) *</label>
                <input type="number" name="recyclability_score" id="recyclability_score" 
                       value="{{ old('recyclability_score') }}" 
                       placeholder="0-100" min="0" max="100" required>
                @error('recyclability_score')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="description">Description *</label>
                <textarea name="description" id="description" rows="3" 
                          placeholder="Describe the material, its properties, and potential uses" 
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="waste_item_id">Link Waste Item *</label>
                <select name="waste_item_id" id="waste_item_id" required>
                    <option value="">Select a waste item to link</option>
                    @foreach($wasteItems as $item)
                       <option value="{{ $item->id }}" {{ old('waste_item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->title }} (Received: {{ $item->received_date ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                <span class="helper-text">Select one waste item that was used to create this material.</span>
                @error('waste_item_id')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="image_path">Material Images *</label>
                <input type="file" name="image_path[]" id="image_path" 
                       accept="image/*" multiple required>
                <span class="helper-text">Select multiple images (PNG, JPG, JPEG up to 5MB each)</span>
                
                <div id="imagePreview" class="image-preview-container"></div>
                
                @error('image_path')
                    <span class="error-text">{{ $message }}</span>
                @enderror
                @error('image_path.*')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width" style="grid-column: 1 / -1; margin-top: 1rem;">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Create Material
                </button>
            </div>
        </form>
    </div>

    <div class="instructions-sidebar">
        <div class="instructions-panel">
            <div class="instructions-header">
                <h3>Creating a Material</h3>
            </div>
            <div class="instructions-content">
                <ul class="instructions-list">
                    <li><strong>Material Name:</strong> Be descriptive and specific about your material</li>
                    <li><strong>Category & Unit:</strong> Select appropriate category and measurement unit</li>
                    <li><strong>Quantity:</strong> Enter the exact amount of material available</li>
                    <li><strong>Recyclability Score:</strong> Rate how recyclable this material is (0-100%)</li>
                    <li><strong>Waste Item Link:</strong> Connect to the original waste item source</li>
                    <li><strong>Images:</strong> Show clear photos of the material from different angles</li>
                </ul>
                <div style="background: #e8f5e8; padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                    <strong>Important:</strong> Materials must be linked to purchased waste items to be eligible for sale in the marketplace.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image_path');
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
});
</script>
@endpush