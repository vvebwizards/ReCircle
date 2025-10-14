<div class="item-card material-card">
    <div class="item-image-container" id="image-container-{{ $material->id }}">
        @if($material->images->count() > 0)
            <img src="{{ asset($material->images->first()->image_path) }}" 
                 alt="{{ $material->name }}" 
                 class="item-image"
                 id="current-image-{{ $material->id }}">
            
            @if($material->images->count() > 1)
                <button class="image-nav-button image-nav-prev" 
                        onclick="prevMaterialImage({{ $material->id }}, {{ $material->images->count() }})">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="image-nav-button image-nav-next" 
                        onclick="nextMaterialImage({{ $material->id }}, {{ $material->images->count() }})">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                
                <div class="image-count-badge">
                    <i class="fa-solid fa-images"></i> {{ $material->images->count() }}
                </div>
                
                <div class="image-position" id="position-{{ $material->id }}">
                    1/{{ $material->images->count() }}
                </div>
            @endif
        @else
            <div class="item-image-placeholder">
                <i class="fa-solid fa-cubes"></i>
            </div>
        @endif
        <div class="category-badge {{ $material->category }}">
            {{ ucfirst($material->category) }}
        </div>
    </div>
    
    <div class="item-content">
        <div class="item-header">
            <h3 class="item-name">{{ $material->name }}</h3>
            <div class="quantity-badge">
                {{ $material->quantity }} {{ strtoupper($material->unit) }}
            </div>
        </div>
        
        <div class="item-meta">
            <div class="meta-item">
                <i class="fa-solid fa-user meta-icon"></i>
                <span>{{ $material->maker->name ?? 'Unknown Maker' }}</span>
            </div>
            <div class="meta-item">
                <i class="fa-solid fa-industry meta-icon"></i>
                <span>Available: {{ $material->quantity }} {{ $material->unit }}</span>
            </div>
        </div>
        
        <p class="item-description">{{ Str::limit($material->description, 100) }}</p>
        
        <div class="score-container">
            <div class="score-header">
                <span class="score-label">Recyclability Score</span>
                <span class="score-value">{{ $material->recyclability_score }}%</span>
            </div>
            <div class="score-bar">
                <div class="score-fill" 
                     style="width: {{ $material->recyclability_score }}%;
                            background: 
                            @if($material->recyclability_score >= 80) #27ae60
                            @elseif($material->recyclability_score >= 60) #f39c12
                            @else #e74c3c
                            @endif;">
                </div>
            </div>
        </div>
        
        <div class="impact-stats">
            @if($material->co2_kg_saved)
                <div class="impact-item">
                    <i class="fa-solid fa-cloud"></i>
                    <span>{{ number_format($material->co2_kg_saved, 1) }}kg CO₂ saved</span>
                </div>
            @endif
            @if($material->landfill_kg_avoided)
                <div class="impact-item">
                    <i class="fa-solid fa-trash"></i>
                    <span>{{ number_format($material->landfill_kg_avoided, 1) }}kg landfill avoided</span>
                </div>
            @endif
        </div>
        
        @if($material->quantity > 0)
        <div class="quantity-selector">
            <label for="quantity-{{ $material->id }}">Quantity ({{ strtoupper($material->unit) }}):</label>
            <div class="quantity-controls">
                <button type="button" class="quantity-btn" onclick="decreaseQuantity({{ $material->id }}, {{ $material->quantity }})">-</button>
                <input type="number" 
                       id="quantity-{{ $material->id }}" 
                       class="quantity-input" 
                       value="1" 
                       min="1" 
                       max="{{ $material->quantity }}"
                       onchange="validateQuantity({{ $material->id }}, {{ $material->quantity }})">
                <button type="button" class="quantity-btn" onclick="increaseQuantity({{ $material->id }}, {{ $material->quantity }})">+</button>
            </div>
        </div>
        @endif
        
        <div class="item-actions">
            <a href="{{ route('buyer.marketplace.show', ['type' => 'material', 'id' => $material->id]) }}" 
               class="btn-action btn-view">
                <i class="fa-solid fa-eye"></i> View Details
            </a>
            @if($material->quantity > 0)
                <button class="btn-action btn-cart" 
                        onclick="addMaterialToCart({{ $material->id }}, '{{ $material->unit }}')">
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