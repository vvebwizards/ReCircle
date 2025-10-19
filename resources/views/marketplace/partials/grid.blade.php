<div class="materials-grid" id="materialsGrid">
    @forelse($wasteItems as $item)
        <div class="material-card" data-id="{{ $item->id }}">
            @php 
                $primary = $item->primary_image_url ?? null;
                $src = $primary; 
                $conditionColors = [
                    'good' => 'bg-green-100 text-green-800',
                    'fixable' => 'bg-yellow-100 text-yellow-800',
                    'scrap' => 'bg-red-100 text-red-800'
                ];
                $conditionIcons = [
                    'good' => 'fa-check-circle',
                    'fixable' => 'fa-wrench',
                    'scrap' => 'fa-recycle'
                ];
            @endphp
            <div class="material-image-wrapper" style="height:180px;">
                @php $photosCount = $item->photos->count(); @endphp
                <div class="card-img-count">
                    <i class="fa-solid fa-camera"></i>
                    <span class="count-val">{{ $photosCount }}</span>
                </div>
                @if($src)
                    <img src="{{ $src }}" alt="{{ $item->title }}" class="card-primary-img" style="object-fit:cover;height:100%;width:100%;" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x240?text=No+Image';">
                @else
                    <div class="card-primary-fallback">
                        <i class="fa-solid fa-image"></i>
                        <span>No Image Available</span>
                    </div>
                @endif
            </div>
            <div class="material-content">
                <div class="material-header">
                    <h3 class="material-name">{{ $item->title }}</h3>
                    <span class="material-badge {{ $conditionColors[$item->condition] ?? '' }}">
                        <i class="fa-solid {{ $conditionIcons[$item->condition] ?? 'fa-circle' }}"></i>
                        {{ ucfirst($item->condition) }}
                    </span>
                </div>
                @if($item->tags && $item->tags->count())
                    <div class="material-tags">
                        <i class="fa-solid fa-tag"></i>
                        @foreach($item->tags as $tag)
                            <span class="tag-badge">#{{ $tag->display_name }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="material-meta">
                    <div class="meta-item" title="Estimated Weight">
                        <i class="fa-solid fa-weight-hanging meta-icon"></i>
                        <span>{{ $item->estimated_weight ? number_format($item->estimated_weight, 2) . ' kg' : '—' }}</span>
                    </div>
                    <div class="meta-item" title="Creation Date">
                        <i class="fa-solid fa-calendar meta-icon"></i>
                        <span>{{ $item->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <p class="material-description">{{ Str::limit($item->notes, 100) }}</p>
                <div class="material-actions" data-id="{{ $item->id }}">
                    <button class="btn-action btn-photos" data-id="{{ $item->id }}" title="See all photos">
                        <i class="fa-solid fa-images"></i>
                        <span>See Photos</span>
                    </button>
                    <button class="btn-action btn-bid" data-id="{{ $item->id }}" title="Make a bid">
                        <i class="fa-solid fa-gavel"></i>
                        <span>Make a Bid</span>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-illustration">
                <div class="empty-icon-wrapper">
                    <i class="fa-solid fa-box-open"></i>
                    <i class="fa-solid fa-recycle empty-icon-accent"></i>
                </div>
            </div>
            <h3 class="empty-title">No Items Available</h3>
            <p class="empty-message">No waste items found matching your filters. Try adjusting your search.</p>
            <div class="empty-actions">
                <a href="{{ route('marketplace.index') }}" class="btn-link">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Reset Filters</span>
                </a>
            </div>
        </div>
    @endforelse
</div>
