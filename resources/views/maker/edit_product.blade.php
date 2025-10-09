@extends('layouts.app')
@push('head')
@vite(['resources/css/create-product.css','resources/css/edit-product.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-form-container">
        <h1 style="margin-bottom: 1.5rem; color: #333;">Edit Product: {{ $product->name }}</h1>
        <form action="{{ route('maker.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="product-form" novalidate>
            @csrf
            @method('PUT')
            
            <div class="form-group full-width">
                <label for="name">Product Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
                @error('name')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full-width">
                <label for="description">Product Description *</label>
                <textarea name="description" id="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                @error('description')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full-width">
                <label for="material_id">Source Material *</label>
                <select name="material_id" id="material_id" required>
                    <option value="">Select a material</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}" {{ old('material_id', $product->material_id) == $material->id ? 'selected' : '' }}>
                            {{ $material->name }} ({{ $material->quantity }} {{ strtoupper($material->unit) }})
                        </option>
                    @endforeach
                </select>
                @error('material_id')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="price">Price (€) *</label>
                <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                @error('price')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', $product->stock) }}" required>
                @error('stock')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="sku">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" readonly style="background-color: #f5f5f5;">
                <span class="helper-text">SKU is auto-generated and cannot be changed</span>
            </div>

            <!-- Static Work Order Display -->
            @if($product->workOrder)
            <div class="form-group full-width">
                <label>Associated Work Order</label>
                <div class="static-field">
                    <strong>WO #{{ $product->workOrder->id }}</strong>
                    <br>
                    <small>Status: {{ $product->workOrder->status }}</small>
                    <br>
                    <small>Completed: {{ $product->workOrder->updated_at->format('M j, Y') }}</small>
                </div>
                <span class="helper-text">Work order association cannot be changed</span>
            </div>
            @endif

            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" name="weight" id="weight" step="0.1" min="0" value="{{ old('weight', $product->weight) }}">
            </div>

            <div class="form-group">
                <label for="warranty_months">Warranty (months)</label>
                <input type="number" name="warranty_months" id="warranty_months" min="0" value="{{ old('warranty_months', $product->warranty_months) }}">
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" required>
                    @foreach(\App\Enums\ProductStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ old('status', $product->status->value) == $status->value ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
                @error('status')<span class="error-text">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full-width">
                <label for="care_instructions">Care Instructions</label>
                <textarea name="care_instructions" id="care_instructions" rows="3">{{ old('care_instructions', $product->care_instructions) }}</textarea>
            </div>

            <div class="form-group full-width">
                <label for="images">Update Product Images</label>
                <input type="file" name="images[]" id="images" multiple accept="image/*">
                <span class="helper-text">Select new images to add (PNG, JPG, JPEG up to 5MB each). Existing images will be kept.</span>
                @error('images')<span class="error-text">{{ $message }}</span>@enderror
                @error('images.*')<span class="error-text">{{ $message }}</span>@enderror
                
                <!-- Current Images Preview -->
                @if($product->images && $product->images->count() > 0)
                <div class="current-images-container">
                    <h4 style="margin: 1rem 0 0.5rem 0; font-size: 0.9rem; color: #666;">Current Images:</h4>
                    <div class="current-images-grid">
                        @foreach($product->images as $image)
                        <div class="current-image-wrapper">
                            <img src="{{ asset($image->image_path) }}" alt="Current product image" class="current-image">
                            <div class="image-actions">
                                <label class="remove-image-checkbox">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                                    Remove
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- New Images Preview -->
                <div id="imagePreview" class="image-preview-container"></div>
            </div>

            <div class="form-group full-width" style="grid-column: 1 / -1; margin-top: 1rem;">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Product</button>
                    <a href="{{ route('maker.products.show', $product->id) }}" class="btn-secondary">Cancel</a>
                    
                    @if($product->status !== \App\Enums\ProductStatus::PUBLISHED && $product->stock > 0)
                    <form action="{{ route('maker.products.publish', $product->id) }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="btn-success">Publish Now</button>
                    </form>
                    @endif
                    
                    <form action="{{ route('maker.products.destroy', $product->id) }}" method="POST" class="inline-form" 
                          onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete Product</button>
                    </form>
                </div>
            </div>
        </form>
    </div>

    <div class="instructions-sidebar">
        <div class="instructions-panel">
            <div class="instructions-header">
                <h3>Editing Product</h3>
            </div>
            <div class="instructions-content">
                <ul class="instructions-list">
                    <li><strong>Name & Description:</strong> Keep your product information up to date</li>
                    <li><strong>Stock Management:</strong> Update stock levels as items sell</li>
                    <li><strong>Pricing:</strong> Adjust pricing based on demand and costs</li>
                    <li><strong>Status:</strong> Change status to manage product visibility</li>
                    <li><strong>Images:</strong> Add new images to showcase your product better</li>
                </ul>
                
                <div class="product-status-info">
                    <h4>Product Status Guide:</h4>
                    <ul>
                        <li><strong>Draft:</strong> Not visible to customers</li>
                        <li><strong>Published:</strong> Visible and available for purchase</li>
                        <li><strong>Sold Out:</strong> Visible but out of stock</li>
                    </ul>
                </div>
                
                <div style="background: #e8f5e8; padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                    <strong>Tip:</strong> Update your product story as you learn more about how customers use and appreciate your sustainable products.
                </div>
            </div>
        </div>
        
        <div class="product-summary-panel">
            <div class="instructions-header">
                <h3>Product Summary</h3>
            </div>
            <div class="instructions-content">
                <div class="summary-item">
                    <strong>SKU:</strong> {{ $product->sku }}
                </div>
                <div class="summary-item">
                    <strong>Created:</strong> {{ $product->created_at->format('M j, Y') }}
                </div>
                <div class="summary-item">
                    <strong>Last Updated:</strong> {{ $product->updated_at->format('M j, Y') }}
                </div>
                <div class="summary-item">
                    <strong>Current Status:</strong> 
                    <span class="status-badge status-{{ strtolower($product->status->name) }}">
                        {{ $product->status->name }}
                    </span>
                </div>
                @if($product->material)
                <div class="summary-item">
                    <strong>Material:</strong> {{ $product->material->name }}
                </div>
                @endif
                @if($product->workOrder)
                <div class="summary-item">
                    <strong>Work Order:</strong> WO #{{ $product->workOrder->id }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

</style>
@endpush

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

    document.querySelectorAll('input[name="remove_images[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const wrapper = this.closest('.current-image-wrapper');
            if (this.checked) {
                wrapper.style.opacity = '0.6';
                wrapper.style.borderColor = '#dc3545';
            } else {
                wrapper.style.opacity = '1';
                wrapper.style.borderColor = '#ddd';
            }
        });
    });
});

function removeImagePreview(element) {
    element.parentElement.remove();
}
</script>
@endpush