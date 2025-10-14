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
                      
                    </div>
                </div>
            </div>

           
            <div class="form-group full-width">
                <div class="pricing-suggestions-section" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; background: #fafafa;">
                    <h5 style="margin-bottom: 1rem; color: #333; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-lightbulb" style="color: #ffc107;"></i> Smart Pricing Assistant
                    </h5>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Product Name</label>
                            <input type="text" class="form-control" id="pricingProductName" 
                                   placeholder="Enter product name" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Category</label>
                            <select class="form-control" id="pricingCategory" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Material::CATEGORIES as $category)
                                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Your Cost (Optional)</label>
                        <input type="number" class="form-control" id="pricingCost" 
                               placeholder="Material + labor cost" step="0.01" min="0" style="width: 100%; max-width: 200px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <small style="color: #666; font-size: 0.875rem;">Total cost of materials and your time</small>
                    </div>

                    <div>
                        <button type="button" id="getPricingSuggestions" class="btn-primary" style="background: #007bff; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer;">
                            <i class="fa-solid fa-chart-line"></i> Get Pricing Suggestions
                        </button>
                        <div id="pricingLoading" class="spinner-border spinner-border-sm" style="display: none; margin-left: 0.5rem;"></div>
                    </div>

                    {{-- Results will appear here --}}
                    <div id="pricingResults" style="margin-top: 1.5rem; display: none;">
                        <div class="card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                            <div class="card-header" style="background: #28a745; color: white; padding: 0.75rem 1rem;">
                                <i class="fa-solid fa-check-circle"></i> Market Analysis Complete
                            </div>
                            <div class="card-body" style="padding: 1rem;">
                                <div id="marketData" style="margin-bottom: 1rem;"></div>
                                <div class="price-suggestions">
                                    <h6 style="margin-bottom: 1rem; color: #333;">Recommended Prices:</h6>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                                        <button type="button" class="price-option" data-price="" style="border: 1px solid #28a745; background: white; color: #28a745; padding: 0.75rem; border-radius: 4px; cursor: pointer; text-align: center;">
                                            <strong>Competitive</strong><br>
                                            $<span id="competitivePrice">0</span>
                                            <small style="display: block; color: #666; margin-top: 0.25rem;">Balanced & Market-Friendly</small>
                                        </button>
                                        <button type="button" class="price-option" data-price="" style="border: 1px solid #ffc107; background: white; color: #ffc107; padding: 0.75rem; border-radius: 4px; cursor: pointer; text-align: center;">
                                            <strong>Premium</strong><br>
                                            $<span id="premiumPrice">0</span>
                                            <small style="display: block; color: #666; margin-top: 0.25rem;">High Quality/Unique Items</small>
                                        </button>
                                        <button type="button" class="price-option" data-price="" style="border: 1px solid #17a2b8; background: white; color: #17a2b8; padding: 0.75rem; border-radius: 4px; cursor: pointer; text-align: center;">
                                            <strong>Quick Sale</strong><br>
                                            $<span id="quickSalePrice">0</span>
                                            <small style="display: block; color: #666; margin-top: 0.25rem;">Fast Turnaround</small>
                                        </button>
                                    </div>
                                </div>
                                <div id="pricingExplanation" style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;"></div>
                            </div>
                        </div>
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
                    <li><strong>Smart Pricing:</strong> Use our pricing assistant to find the optimal price</li>
                    <li><strong>Images:</strong> Show multiple angles and details</li>
                    <li><strong>Stock:</strong> Set realistic available quantities</li>
                </ul>
                <div style="background: #e8f5e8; padding: 0.75rem; border-radius: 4px; margin-top: 1rem;">
                    <strong>💡 Smart Pricing Tip:</strong> Use the pricing assistant to analyze market trends and suggest competitive prices based on similar upcycled products.
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

    // Smart Pricing Assistant JavaScript
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

    const mainProductName = document.getElementById('name');
    
    if (mainProductName) {
        mainProductName.addEventListener('blur', function() {
            if (this.value) {
                document.getElementById('pricingProductName').value = this.value;
            }
        });
    }
});
</script>
@endpush