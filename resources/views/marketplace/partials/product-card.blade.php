<div class="item-card product-card">
    <div class="item-image-container">
        @if($product->images->count() > 0)
            <img src="{{ asset($product->images->first()->image_path) }}" 
                 alt="{{ $product->name }}" 
                 class="item-image">
            @if($product->is_featured)
                <div class="featured-badge">
                    <i class="fa-solid fa-star"></i> Featured
                </div>
            @endif
        @else
            <div class="item-image-placeholder">
                <i class="fa-solid fa-cube"></i>
            </div>
        @endif
    </div>
    
    <div class="item-content">
        <div class="item-header">
            <h3 class="item-name">{{ $product->name }}</h3>
            <div class="item-price">${{ number_format($product->price, 2) }}</div>
        </div>
        
        <div class="item-meta">
            <div class="meta-item">
                <i class="fa-solid fa-user meta-icon"></i>
                <span>{{ $product->maker->name ?? 'Unknown Maker' }}</span>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-box meta-icon"></i>
                <span>{{ $product->stock }} available</span>
            </div>
            @if($product->stock > 0)
                <div class="stock-badge in-stock">
                    In Stock ({{ $product->stock }})
                </div>
            @else
                <div class="stock-badge out-of-stock">
                    Out of Stock
                </div>
            @endif
        </div>
        
        <p class="item-description">{{ Str::limit($product->description, 100) }}</p>
        
        <div class="impact-stats">
            @php
                $totalCO2 = $product->materials ? $product->materials->sum('co2_kg_saved') : 0;
                $totalLandfill = $product->materials ? $product->materials->sum('landfill_kg_avoided') : 0;
            @endphp
            @if($totalCO2 > 0)
                <div class="impact-item">
                    <i class="fa-solid fa-cloud"></i>
                    <span>{{ number_format($totalCO2, 1) }}kg CO₂ saved</span>
                </div>
            @endif
            @if($totalLandfill > 0)
                <div class="impact-item">
                    <i class="fa-solid fa-trash"></i>
                    <span>{{ number_format($totalLandfill, 1) }}kg landfill avoided</span>
                </div>
            @endif
        </div>
        
        @if($product->stock > 0)
        <div class="quantity-selector">
            <label for="quantity-product-{{ $product->id }}">Quantity:</label>
            <div class="quantity-controls">
                <button type="button" class="quantity-btn" onclick="decreaseProductQuantity({{ $product->id }}, {{ $product->stock }})">-</button>
                <input type="number" 
                       id="quantity-product-{{ $product->id }}" 
                       class="quantity-input" 
                       value="1" 
                       min="1" 
                       max="{{ $product->stock }}"
                       onchange="validateProductQuantity({{ $product->id }}, {{ $product->stock }})">
                <button type="button" class="quantity-btn" onclick="increaseProductQuantity({{ $product->id }}, {{ $product->stock }})">+</button>
            </div>
        </div>
        @endif
        
        <div class="item-actions">
            <a href="{{ route('buyer.marketplace.show', ['type' => 'product', 'id' => $product->id]) }}" 
               class="btn-action btn-view">
                <i class="fa-solid fa-eye"></i> View Details
            </a>
            @if($product->stock > 0)
                <button class="btn-action btn-cart" 
                        onclick="addProductToCart({{ $product->id }})">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
            @else
                <button class="btn-action btn-disabled" disabled>
                    <i class="fa-solid fa-cart-plus"></i> Out of Stock
                </button>
            @endif
        </div>
    </div>
</div>