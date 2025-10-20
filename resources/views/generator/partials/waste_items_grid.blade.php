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
                <div class="card-overlay">
                    <div class="card-overlay-actions">
                        <button class="overlay-btn view-photos" title="View all photos">
                            <i class="fa-solid fa-images"></i>
                        </button>
                    </div>
                </div>
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
                @php
                    $loc = data_get($item, 'location');
                    $lat = null; $lng = null;
                    if (is_array($loc)) {
                        if (isset($loc['lat'])) $lat = $loc['lat'];
                        elseif (isset($loc['latitude'])) $lat = $loc['latitude'];
                        elseif (isset($loc[1])) $lat = $loc[1];

                        if (isset($loc['lng'])) $lng = $loc['lng'];
                        elseif (isset($loc['lon'])) $lng = $loc['lon'];
                        elseif (isset($loc['longitude'])) $lng = $loc['longitude'];
                        elseif (isset($loc[0])) $lng = $loc[0];
                    }
                @endphp
                @if($addr = data_get($item, 'location.address'))
                    <div class="material-address meta-item" title="{{ $addr }}">
                        <i class="fa-solid fa-location-dot meta-icon"></i>
                        <span>{{ \Illuminate\Support\Str::limit($addr, 60) }}</span>
                    </div>
                @elseif($lat && $lng)
                    <div class="material-address meta-item js-revgeo" data-lat="{{ $lat }}" data-lng="{{ $lng }}" title="">
                        <i class="fa-solid fa-location-dot meta-icon"></i>
                        <span>Loading location…</span>
                    </div>
                @endif
                <p class="material-description">{{ Str::limit($item->notes, 100) }}</p>
                <div class="material-actions" data-id="{{ $item->id }}">
                    <button class="btn-action btn-view" data-id="{{ $item->id }}" title="View Details">
                        <i class="fa-solid fa-eye"></i>
                        <span>View</span>
                    </button>
                    <button class="btn-action btn-edit" data-id="{{ $item->id }}" title="Edit Item">
                        <i class="fa-solid fa-edit"></i>
                        <span>Edit</span>
                    </button>
                    <button type="button" class="btn-action btn-delete" data-id="{{ $item->id }}" title="Delete Item">
                        <i class="fa-solid fa-trash"></i>
                        <span>Delete</span>
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
            <h3 class="empty-title">No Waste Items Yet</h3>
            <p class="empty-message">Start managing your waste items by creating your first one. Track conditions, weights, and more!</p>
            <div class="empty-actions">
                <a href="#" class="btn-create open-create-modal">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Your First Item</span>
                </a>
                <a href="{{ route('generator.waste-items.create') }}" class="btn-link">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Learn More</span>
                </a>
            </div>
        </div>
    @endforelse
</div>

@once
    @push('scripts')
    <script>
    (function(){
        // Reverse geocode elements with class js-revgeo using Nominatim
        const revgeoEls = () => Array.from(document.querySelectorAll('.js-revgeo'));
        const cacheKey = (lat, lng) => `revgeo:${lat}:${lng}`;

        async function fetchAddress(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!resp.ok) throw new Error('Reverse geocode failed');
            return resp.json();
        }

        function fillElement(el, displayName) {
            const span = el.querySelector('span');
            if (span) span.textContent = displayName.length > 60 ? displayName.slice(0,57) + '...' : displayName;
            el.setAttribute('title', displayName);
        }

        async function processQueue(elems) {
            for (const el of elems) {
                try {
                    const lat = el.getAttribute('data-lat');
                    const lng = el.getAttribute('data-lng');
                    if (!lat || !lng) continue;
                    const key = cacheKey(lat, lng);
                    const cached = sessionStorage.getItem(key);
                    if (cached) {
                        fillElement(el, cached);
                        continue;
                    }
                    // Respect Nominatim usage policy: at most 1 request per second
                    const data = await fetchAddress(lat, lng);
                    const display = (data && data.display_name) ? data.display_name : `${lat}, ${lng}`;
                    try { sessionStorage.setItem(key, display); } catch (err) { /* ignore */ }
                    fillElement(el, display);
                    // wait ~1100ms
                    await new Promise(r => setTimeout(r, 1100));
                } catch (err) {
                    console.warn('Reverse geocode error', err);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            const els = revgeoEls();
            if (els.length === 0) return;
            // Process in background, non-blocking
            processQueue(els);
        });
    })();
    </script>
    @endpush
@endonce
