@extends('layouts.app')

@push('head')
@vite(['resources/css/material-create.css', 'resources/css/update-material.css', 'resources/js/update-material.js'])
<style>
   
</style>
@endpush

@section('content')
<main class="dashboard">
  <div class="container">
    <h1 class="dash-title"><i class="fa-solid fa-edit"></i> Edit Material</h1>
    <p class="dash-sub">Update your material information and images.</p>
    @if(session('success'))
    <div class="alert alert-success">
        <strong>Success!</strong> {{ session('success') }}
    </div>
    @endif

    <div class="material-container">
      <div class="material-form-container">
        <form action="{{ route('maker.materials.update', $material->id) }}" method="POST" enctype="multipart/form-data" class="material-form" id="materialForm" novalidate>
          @csrf
          @method('PUT')
          
          <div class="form-group full-width {{ $errors->has('name') ? 'has-error' : '' }}">
            <label for="name">Material Name *</label>
            <input type="text" name="name" id="name" 
                   value="{{ old('name', $material->name) }}" 
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
                <option value="{{ $category }}" {{ old('category', $material->category) == $category ? 'selected' : '' }}>
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
                <option value="{{ $unit }}" {{ old('unit', $material->unit) == $unit ? 'selected' : '' }}>
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
                   value="{{ old('quantity', $material->quantity) }}" 
                   class="{{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                   placeholder="0.00" step="0.01" min="0" required>
            @error('quantity')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group {{ $errors->has('recyclability_score') ? 'has-error' : '' }}">
            <label for="recyclability_score">Recyclability Score (%) *</label>
            <input type="number" name="recyclability_score" id="recyclability_score" 
                   value="{{ old('recyclability_score', $material->recyclability_score) }}" 
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
                      required>{{ old('description', $material->description) }}</textarea>
            @error('description')
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
                   <option value="{{ $item->id }}" {{ old('waste_item_id', $material->waste_item_id) == $item->id ? 'selected' : '' }}>
                        {{ $item->title }} (Received: {{ $item->received_date ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            @error('waste_item_id')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width existing-images {{ $errors->has('remove_images') ? 'has-error' : '' }}">
            <label>Current Images</label>
            <div class="image-preview-container">
              @foreach($material->images as $image)
                <div class="existing-image-item">
                  <img src="{{ asset($image->image_path) }}" 
                       alt="Material image" 
                       class="image-preview">
                  <div>
                    <p class="mb-1">Image {{ $loop->iteration }}</p>
                    <label class="checkbox-label">
                      <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"
                             {{ in_array($image->id, (array)old('remove_images', [])) ? 'checked' : '' }}>
                      Remove this image
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
            @if($material->images->isEmpty())
              <p class="text-muted">No images uploaded yet.</p>
            @endif
            @error('remove_images')
                <small class="error-text">{{ $message }}</small>
            @enderror
            @error('remove_images.*')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width {{ $errors->has('image_path') ? 'has-error' : '' }}">
            <label for="image_path">Add New Images</label>
            <input type="file" name="image_path[]" id="image_path" 
                   class="{{ $errors->has('image_path') ? 'is-invalid' : '' }}"
                   accept="image/*" multiple>
            <small class="helper-text">Select additional images (Ctrl+Click). Max 2MB each.</small>
            
            <div class="image-preview-container" id="newImagePreview"></div>
            
            @error('image_path')
                <small class="error-text">{{ $message }}</small>
            @enderror
            @error('image_path.*')
                <small class="error-text">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group full-width">
            <button type="submit" class="btn btn-primary mt-1">
              <i class="fa-solid fa-save"></i> Update Material
            </button>
            <a href="{{ route('maker.materials.index') }}" class="btn btn-secondary mt-1">
              <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
          </div>
        </form>
      </div>

      <div class="instructions-sidebar">
        <div class="instructions-panel">
          <div class="instructions-header">
            <h3><i class="fa-solid fa-info-circle"></i> Editing Instructions</h3>
          </div>
          <div class="instructions-content">
            <ul class="instructions-list">
              <li>Update material information as needed</li>
              <li>Check "Remove this image" to delete unwanted images</li>
              <li>Add new images by selecting files below</li>
              <li>All changes will be reflected immediately after saving</li>
              <li>Ensure recyclability score remains accurate</li>
              <li>Required fields are marked with *</li>
            </ul>
            
            @if($errors->any())
            <div class="instruction-warning">
              <strong><i class="fa-solid fa-exclamation-triangle"></i> Form Errors:</strong> 
              Please correct the highlighted fields above before submitting.
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
