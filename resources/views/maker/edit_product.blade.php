@extends('layouts.app')

@push('head')
@vite(['resources/css/create-product.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-form-container">
        <h1 style="margin-bottom: 1.5rem; color: #333;">Edit Product: {{ $product->name }}</h1>
        
        @if($errors->any())
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <h4>Please fix the following errors:</h4>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                        @php
                            $oldMaterials = old('materials', []);
                            $hasOldMaterials = !empty($oldMaterials);
                            
                            if ($hasOldMaterials) {
                                // Handle old input data structure
                                $materialsData = [];
                                foreach ($oldMaterials as $materialId => $materialData) {
                                    $materialModel = \App\Models\Material::find($materialId);
                                    if ($materialModel) {
                                        $materialsData[$materialId] = [
                                            'id' => $materialId,
                                            'name' => $materialModel->name,
                                            'quantity_used' => $materialData['quantity_used'] ?? 0,
                                            'unit' => $materialModel->unit,
                                            'available' => $materialModel->quantity + ($product->materials->find($materialId)->pivot->quantity_used ?? 0)
                                        ];
                                    }
                                }
                            } else {
                                // Handle existing product materials
                                $materialsData = $product->materials->mapWithKeys(function($material) {
                                    return [
                                        $material->id => [
                                            'id' => $material->id,
                                            'name' => $material->name,
                                            'quantity_used' => $material->pivot->quantity_used,
                                            'unit' => $material->pivot->unit,
                                            'available' => $material->quantity + $material->pivot->quantity_used
                                        ]
                                    ];
                                })->toArray();
                            }
                        @endphp

                        @foreach($materialsData as $materialId => $material)
                        <div class="material-row" data-material-id="{{ $materialId }}">
                            <input type="hidden" name="materials[{{ $materialId }}][id]" value="{{ $materialId }}">
                            <span class="material-name">{{ $material['name'] ?? 'Unknown Material' }}</span>
                            <span class="material-available">{{ $material['available'] ?? 0 }} {{ $material['unit'] ?? 'unit' }}</span>
                            <input type="number" 
                                   name="materials[{{ $materialId }}][quantity_used]" 
                                   min="0.01" 
                                   max="{{ $material['available'] ?? 0 }}"
                                   step="0.01" 
                                   value="{{ $material['quantity_used'] ?? 0 }}"
                                   placeholder="0.00"
                                   required
                                   class="quantity-input">
                            <button type="button" class="remove-material" onclick="removeMaterial('{{ $materialId }}', this)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
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
                
                <div id="imagePreview" class="image-preview-container"></div>
            </div>

            <div class="form-group full-width" style="grid-column: 1 / -1; margin-top: 1rem;">
                <button type="submit" class="btn-primary">Update Product</button>
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
                    <li><strong>Materials Update:</strong> You can add or remove materials. Quantities will be automatically adjusted.</li>
                    <li><strong>Smart Pricing:</strong> Re-analyze pricing if you've changed materials or market conditions</li>
                    <li><strong>Images:</strong> Add new images or remove existing ones</li>
                    <li><strong>Stock Management:</strong> Update available quantities as you sell</li>
                    <li><strong>Status:</strong> Change product status to control visibility</li>
                </ul>
                <div style="background: #e8f5e8; padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                    <strong>💡 Update Tip:</strong> When changing materials, the system automatically returns unused quantities to your inventory and deducts new quantities.
                </div>

                <div class="product-summary" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                    <h4 style="margin-bottom: 1rem; color: #333;">Product Summary</h4>
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
                    @if($product->materials->count() > 0)
                    <div class="summary-item">
                        <strong>Current Materials:</strong>
                        <ul style="margin: 0.25rem 0; padding-left: 1rem;">
                            @foreach($product->materials as $material)
                            <li>{{ $material->name }} ({{ $material->pivot->quantity_used }} {{ $material->pivot->unit }})</li>
                            @endforeach
                        </ul>
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const materialsSearch = document.getElementById('materials-search');
    const materialsDropdown = document.getElementById('materials-dropdown');
    const selectedMaterialsList = document.getElementById('selected-materials-list');
    const selectedMaterials = new Set();

    // Initialize with existing materials
    @foreach($materialsData as $materialId => $material)
        selectedMaterials.add('{{ $materialId }}');
    @endforeach

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

    // Remove image checkboxes styling
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

        materialsSearch.addEventListener('input', filterMaterials);

        materialsDropdown.addEventListener('click', function(e) {
            const materialOption = e.target.closest('.material-option');
            if (materialOption) {
                const materialId = materialOption.dataset.id;
                
                if (!selectedMaterials.has(materialId)) {
                    addMaterialRow(materialOption);
                    selectedMaterials.add(materialId);
                }
                
                materialsSearch.value = '';
                materialsDropdown.style.display = 'none';
            }
        });

        function filterMaterials() {
            const searchTerm = materialsSearch.value.toLowerCase();
            const options = materialsDropdown.querySelectorAll('.material-option');
            
            options.forEach(option => {
                const materialName = option.querySelector('.material-name').textContent.toLowerCase();
                const materialId = option.dataset.id;
                
                // Hide already selected materials
                if (selectedMaterials.has(materialId)) {
                    option.style.display = 'none';
                    return;
                }
                
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
        row.setAttribute('data-material-id', materialId);
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

    // Smart Pricing functionality
    const pricingBtn = document.getElementById('getPricingSuggestions');
    const loadingSpinner = document.getElementById('pricingLoading');
    const resultsDiv = document.getElementById('pricingResults');

    if (pricingBtn) {
        pricingBtn.addEventListener('click', function() {
            const productName = document.getElementById('pricingProductName').value;
            const category = document.getElementById('pricingCategory').value;

            if (!productName || !category) {
                alert('Please enter both product name and category');
                return;
            }

            pricingBtn.disabled = true;
            pricingBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analyzing Market...';
            loadingSpinner.style.display = 'inline-block';
            resultsDiv.style.display = 'none';

            const costPrice = document.getElementById('pricingCost').value || null;

            fetch('{{ route("maker.products.pricing-suggestions") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_name: productName,
                    category: category,
                    cost_price: costPrice
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayPricingSuggestions(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error getting pricing suggestions. Please try again.');
            })
            .finally(() => {
                pricingBtn.disabled = false;
                pricingBtn.innerHTML = '<i class="fa-solid fa-chart-line"></i> Get Pricing Suggestions';
                loadingSpinner.style.display = 'none';
            });
        });
    }

    function displayPricingSuggestions(data) {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }

        const marketDiv = document.getElementById('marketData');
        const isRealData = data.is_real_data || data.market_data?.is_real_data;
        const source = data.market_data?.source || 'market analysis';
        
        marketDiv.innerHTML = `
            <div class="market-insights">
                <h6 style="margin-bottom: 0.5rem; color: #333;">Market Insights:</h6>
                <p style="margin: 0.25rem 0;">📊 Similar items sell for: <strong>$${data.market_data.price_range}</strong></p>
                <p style="margin: 0.25rem 0;">📈 Average market price: <strong>$${data.market_data.average_price}</strong></p>
                <p style="margin: 0.25rem 0;">🔍 Based on ${data.market_data.sample_size} market listings</p>
                <small style="color: #666;">
                    ${isRealData ? 
                      `✅ Real data from ${source}` : 
                      '💡 Using estimated market data'}
                </small>
            </div>
        `;

        document.getElementById('competitivePrice').textContent = data.competitive_price;
        document.getElementById('premiumPrice').textContent = data.premium_price;
        document.getElementById('quickSalePrice').textContent = data.quick_sale_price;

        document.querySelectorAll('.price-option').forEach((btn, index) => {
            const prices = [data.competitive_price, data.premium_price, data.quick_sale_price];
            btn.setAttribute('data-price', prices[index]);
            btn.onclick = function() {
                const priceField = document.getElementById('price');
                if (priceField) {
                    priceField.value = this.getAttribute('data-price');
                }
                highlightSelectedPrice(this);
            };
        });

        const explanationDiv = document.getElementById('pricingExplanation');
        if (data.explanation) {
            explanationDiv.innerHTML = `<strong>💡 Insight:</strong> ${data.explanation}`;
        }

        resultsDiv.style.display = 'block';
        resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function highlightSelectedPrice(selectedButton) {
        document.querySelectorAll('.price-option').forEach(btn => {
            btn.style.background = 'white';
            btn.style.color = '';
        });
        
        if (selectedButton.textContent.includes('Competitive')) {
            selectedButton.style.background = '#28a745';
            selectedButton.style.color = 'white';
        } else if (selectedButton.textContent.includes('Premium')) {
            selectedButton.style.background = '#ffc107';
            selectedButton.style.color = 'white';
        } else {
            selectedButton.style.background = '#17a2b8';
            selectedButton.style.color = 'white';
        }
        
        showPriceSelectedMessage(selectedButton.getAttribute('data-price'));
    }

    function showPriceSelectedMessage(price) {
        const message = document.createElement('div');
        message.style.cssText = 'background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 4px; margin-top: 0.5rem; border: 1px solid #c3e6cb;';
        message.innerHTML = `<i class="fa-solid fa-check"></i> Price set to $${price}`;
        
        const container = document.querySelector('.price-suggestions');
        
        const existingAlerts = container.querySelectorAll('[style*="background: #d4edda"]');
        existingAlerts.forEach(alert => alert.remove());
        
        container.appendChild(message);
        
        setTimeout(() => {
            if (message.parentNode) {
                message.remove();
            }
        }, 3000);
    }

    // Auto-fill pricing fields from product data
    const mainProductName = document.getElementById('name');
    if (mainProductName) {
        mainProductName.addEventListener('blur', function() {
            if (this.value) {
                document.getElementById('pricingProductName').value = this.value;
            }
        });
    }

    // Initialize pricing category based on first material
    @if($product->materials->count() > 0)
        document.getElementById('pricingCategory').value = '{{ $product->materials->first()->category }}';
    @endif
});
</script>
@endpush