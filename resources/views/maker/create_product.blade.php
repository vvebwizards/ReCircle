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

            <div class="form-group full-width">
                <label for="material_id">Source Material *</label>
                <select name="material_id" id="material_id" required>
                    <option value="">Select a material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                            {{ $material->name }} ({{ $material->quantity }} {{ strtoupper($material->unit) }})
                        </option>
                    @endforeach
                </select>
                @error('material_id')<span class="error-text">{{ $message }}</span>@enderror
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

            @if($workOrders->count() > 0)
            <div class="form-group">
                <label for="work_order_id">Work Order</label>
                <select name="work_order_id" id="work_order_id">
                    <option value="">No work order</option>
                    @foreach($workOrders as $workOrder)
                        <option value="{{ $workOrder->id }}" {{ old('work_order_id') == $workOrder->id ? 'selected' : '' }}>
                            WO #{{ $workOrder->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

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
                <a href="{{ route('maker.products') }}" style="margin-left: 1rem; color: #666; text-decoration: none;">Cancel</a>
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
                    <li><strong>Source Material:</strong> Select the recycled material used</li>
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
});
</script>
@endpush