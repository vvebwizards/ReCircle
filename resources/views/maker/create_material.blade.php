@extends('layouts.app')
@push('head')
@vite(['resources/css/material-create.css', 'resources/js/material-create.js'])
@endpush

@section('content')
<main class="dashboard">
  <div class="container">
    <h1 class="dash-title"><i class="fa-solid fa-cube"></i> Create New Material</h1>
    <p class="dash-sub">Transform waste items into valuable materials. Complete the form below to register a new material.</p>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="material-container">
      <div class="material-form-container">
        <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data" class="material-form" id="materialForm" novalidate>
          @csrf
          
          <div class="form-group full-width {{ $errors->has('name') ? 'has-error' : '' }}">
            <label for="name">Material Name *</label>
            <input type="text" name="name" id="name" 
                   value="{{ old('name') }}" 
                   class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                   placeholder="e.g., Recycled Plastic Pellets" 
                   required>
            @error('name')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
            <label for="category">Category *</label>
            <select name="category" id="category" 
                    class="{{ $errors->has('category') ? 'is-invalid' : '' }}" 
                    required>
              <option value="">Select a category</option>
              @foreach(\App\Models\Material::CATEGORIES as $category)
                <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                  {{ ucfirst($category) }}
                </option>
              @endforeach
            </select>
            @error('category')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group {{ $errors->has('unit') ? 'has-error' : '' }}">
            <label for="unit">Unit *</label>
            <select name="unit" id="unit" 
                    class="{{ $errors->has('unit') ? 'is-invalid' : '' }}" 
                    required>
              <option value="">Select unit</option>
              @foreach(\App\Models\Material::UNITS as $unit)
                <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>
                  {{ strtoupper($unit) }}
                </option>
              @endforeach
            </select>
            @error('unit')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group {{ $errors->has('quantity') ? 'has-error' : '' }}">
            <label for="quantity">Quantity *</label>
            <input type="number" name="quantity" id="quantity" 
                   value="{{ old('quantity') }}" 
                   class="{{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                   placeholder="0.00" step="0.01" min="0" required>
            @error('quantity')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group {{ $errors->has('recyclability_score') ? 'has-error' : '' }}">
            <label for="recyclability_score">Recyclability Score (%) *</label>
            <input type="number" name="recyclability_score" id="recyclability_score" 
                   value="{{ old('recyclability_score') }}" 
                   class="{{ $errors->has('recyclability_score') ? 'is-invalid' : '' }}"
                   placeholder="0-100" min="0" max="100" required>
            @error('recyclability_score')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width {{ $errors->has('description') ? 'has-error' : '' }}">
            <label for="description">Description *</label>
            <textarea name="description" id="description" rows="3" 
                      class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                      placeholder="Describe the material, its properties, and potential uses" 
                      required>{{ old('description') }}</textarea>
            @error('description')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width {{ $errors->has('image_path') ? 'has-error' : '' }}">
            <label for="image_path">Material Images *</label>
            <input type="file" name="image_path[]" id="image_path" 
                   class="{{ $errors->has('image_path') ? 'is-invalid' : '' }}"
                   accept="image/*" multiple>
            <small class="helper-text">Select multiple images (Ctrl+Click). Recommended: Square images, max 2MB each.</small>
            
            <div class="image-preview-container" id="imagePreview"></div>
            
            @error('image_path')
                <small class="error-text">{{ $message }}</small>
            @enderror
            @error('image_path.*')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width {{ $errors->has('waste_item_id') ? 'has-error' : '' }}">
            <label for="waste_item_id">Link Waste Item *</label>
            <select name="waste_item_id" id="waste_item_id" 
                    class="{{ $errors->has('waste_item_id') ? 'is-invalid' : '' }}" 
                    required>
                <option value="">Select a waste item to link</option>
                @foreach($wasteItems as $item)
                   <option value="{{ $item->id }}" {{ old('waste_item_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->title }} (Received: {{ $item->received_date ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            <small class="helper-text">Select one waste item that was used to create this material.</small>
            @error('waste_item_id')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width">
            <button type="submit" class="btn btn-primary mt-1">
              <i class="fa-solid fa-save"></i> Create Material
            </button>
          </div>
        </form>
      </div>

          <div class="instructions-sidebar">
        <div class="instructions-panel">
          <div class="instructions-header">
            <h3><i class="fa-solid fa-info-circle"></i> Important Instructions</h3>
            <button class="instructions-toggle" aria-label="Toggle instructions">
              <i class="fa-solid fa-chevron-down"></i>
            </button>
          </div>
          <div class="instructions-content">
            <ul class="instructions-list">
              <li>Materials must be linked to purchased waste items to be eligible for sale</li>
              <li>Select the waste items that were used to create this material</li>
              <li>Provide accurate recyclability scores for better marketplace visibility</li>
              <li>Upload clear images to help potential buyers evaluate your material</li>
              <li>Quantity should reflect the actual amount of material available</li>
              
            </ul>
            <div class="instruction-warning">
              <strong><i class="fa-solid fa-exclamation-triangle"></i> Important:</strong> 
              Materials not linked to purchased waste items cannot be listed for sale in the marketplace. 
              Ensure you select at least one waste item if you plan to sell this material.
            </div>
          </div>
        </div>
    </div>
  </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image_path');
    const imagePreview = document.getElementById('imagePreview');
    let imageCounter = 0;
    
    imageInput.addEventListener('change', function() {
        imagePreview.innerHTML = ''; 
        imageCounter = 0;
        
        if (this.files) {
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewWrapper = document.createElement('div');
                        previewWrapper.className = 'image-preview-wrapper';
                        previewWrapper.setAttribute('data-index', imageCounter);
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '×';
                        removeBtn.onclick = function() {
                            previewWrapper.remove();
                            // Mettre à jour l'input file
                            updateFileInput();
                        };
                        
                        const orderBadge = document.createElement('span');
                        orderBadge.className = 'image-order';
                        orderBadge.textContent = imageCounter + 1;
                        orderBadge.style.position = 'absolute';
                        orderBadge.style.top = '5px';
                        orderBadge.style.left = '5px';
                        orderBadge.style.background = 'rgba(0,0,0,0.7)';
                        orderBadge.style.color = 'white';
                        orderBadge.style.padding = '2px 6px';
                        orderBadge.style.borderRadius = '3px';
                        orderBadge.style.fontSize = '12px';
                        
                        previewWrapper.appendChild(img);
                        previewWrapper.appendChild(removeBtn);
                        previewWrapper.appendChild(orderBadge);
                        imagePreview.appendChild(previewWrapper);
                        
                        imageCounter++;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    function updateFileInput() {
        // Cette fonction peut être implémentée pour gérer la suppression d'images
    }
});
</script>
@endpush
@endsection