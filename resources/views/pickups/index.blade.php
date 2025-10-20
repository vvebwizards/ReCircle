@extends('layouts.app')

@section('content')
<style>
    .modern-pickups-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .pickups-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .page-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .page-title {
        color: #1e293b;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .download-buttons {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }
    
    .download-btn {
        background: #059669;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .download-btn:hover {
        background: #047857;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    
   
    
    
    
    .download-btn.excel {
        background: #16a34a;
    }
    
    .download-btn.excel:hover {
        background: #15803d;
    }
    
    .download-btn.chat {
        background: #3b82f6;
    }
    
    .download-btn.chat:hover {
        background: #2563eb;
    }
    
    .filters-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    
    .filters-title {
        color: #1e293b;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-label {
        color: #374151;
        font-weight: 500;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    .filter-input, .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }
    
    .filter-buttons {
        display: flex;
        gap: 1rem;
        align-items: center;
        justify-content: flex-end;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .filter-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-btn.primary {
        background: #059669;
        color: white;
    }
    
    .filter-btn.primary:hover {
        background: #047857;
        transform: translateY(-1px);
    }
    
    .filter-btn.secondary {
        background: #6b7280;
        color: white;
    }
    
    .filter-btn.secondary:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }
    
    .filter-btn.clear {
        background: #ef4444;
        color: white;
    }
    
    .filter-btn.clear:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }
    
    .title-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }
    
    .page-subtitle {
        color: #64748b;
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }
    
    .btn-new-pickup {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
        font-weight: 600;
        font-size: 1rem;
        padding: 1rem 2rem;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .btn-new-pickup:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 255, 136, 0.5);
        color: #ffffff;
        text-decoration: none;
    }
    
    .pickups-grid {
        display: grid;
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .pickup-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .pickup-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 20px 20px 0 0;
    }
    
    .pickup-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        border-color: rgba(0, 255, 136, 0.3);
    }
    
    .pickup-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .pickup-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .pickup-id {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-scheduled {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #ffffff;
    }
    
    .status-assigned {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
    }
    
    .status-in-transit {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: #ffffff;
    }
    
    .status-picked {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: #ffffff;
    }
    
    .pickup-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-item {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .detail-value {
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 600;
    }
    
    .pickup-notes {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .notes-label {
        color: #059669;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .notes-content {
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .pickup-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }
    
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
    }
    
    .btn-select {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
    }
    
    .btn-chat {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: #ffffff;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        text-decoration: none;
    }
    
    .empty-state {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 4rem 3rem;
        text-align: center;
        color: #64748b;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-top: 2rem;
    }
    
    .empty-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #64748b, #475569);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.8rem;
        color: #ffffff;
    }
    
    .empty-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #1e293b;
    }
    
    .empty-description {
        font-size: 1rem;
        color: #64748b;
    }
    
    @media (max-width: 768px) {
        .pickups-container {
            padding: 0 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.6rem;
            flex-direction: column;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .title-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
        }
        
        .pickup-card {
            padding: 1.5rem;
        }
        
        .pickup-details {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .pickup-actions {
            justify-content: center;
        }
        
        .pickups-grid {
            gap: 1.5rem;
        }
    }
</style>

<div class="modern-pickups-page">
    <div class="pickups-container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <span class="title-icon">
                            <i class="fa-solid fa-truck"></i>
                        </span>
                        Pickups Management
    </h1>
                    <p class="page-subtitle">Manage and track all your waste pickups</p>
                </div>
                
                <div class="download-buttons">
                    <a href="{{ route('chat.index') }}" class="download-btn chat">
                        <i class="fa-solid fa-comments"></i>
                        Chat
                    </a>
                    <a href="{{ route('pickups.download.excel') }}" class="download-btn excel">
                        <i class="fa-solid fa-file-excel"></i>
                        Excel
                    </a>
                </div>
               
            </div>
  </div>

  <!-- Section des filtres -->
  <div class="filters-section">
      <h3 class="filters-title">
          <i class="fa-solid fa-filter"></i>
          Filtres de recherche
      </h3>
      
      <form method="GET" action="{{ route('pickups.index') }}" id="filters-form">
          <div class="filters-grid">
              <!-- Recherche textuelle -->
              <div class="filter-group">
                  <label class="filter-label" for="search">
                      <i class="fa-solid fa-search"></i>
                      Recherche
                  </label>
                  <input type="text" 
                         id="search" 
                         name="search" 
                         class="filter-input" 
                         placeholder="Adresse, code de suivi, produit..."
                         value="{{ request('search') }}">
              </div>

              <!-- Filtre par statut -->
              <div class="filter-group">
                  <label class="filter-label" for="status">
                      <i class="fa-solid fa-circle-info"></i>
                      Statut
                  </label>
                  <select id="status" name="status" class="filter-select">
                      <option value="">Tous les statuts</option>
                      @foreach($statuses as $status)
                          <option value="{{ $status }}" 
                                  {{ request('status') === $status ? 'selected' : '' }}>
                              {{ ucfirst(str_replace('_', ' ', $status)) }}
                          </option>
                      @endforeach
                  </select>
              </div>

          </div>

          <div class="filter-buttons">
              <button type="submit" class="filter-btn primary">
                  <i class="fa-solid fa-search"></i>
                  Filtrer
              </button>
              
              <a href="{{ route('pickups.index') }}" class="filter-btn secondary">
                  <i class="fa-solid fa-refresh"></i>
                  Réinitialiser
              </a>
              
              <button type="button" class="filter-btn clear" onclick="clearAllFilters()">
                  <i class="fa-solid fa-times"></i>
                  Effacer tout
              </button>
          </div>
      </form>
  </div>

  @if($pickups->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="empty-title">No Pickups Yet</h3>
                <p class="empty-description">Create your first pickup to get started with waste management.</p>
    </div>
  @else
            <div class="pickups-grid">
          @foreach($pickups as $p)
                    <div class="pickup-card">
                        <div class="pickup-header">
                            <div>
                                <div class="pickup-title">{{ $p->wasteItem->title ?? '—' }}</div>
                            </div>
                            <span class="status-badge status-{{ $p->status }}">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </div>

                        <div class="pickup-details">
                            <div class="detail-item">
                                <div class="detail-label">Window Start</div>
                                <div class="detail-value">
                                    {{ \Illuminate\Support\Carbon::parse($p->window_start)->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Window End</div>
                                <div class="detail-value">
                                    {{ \Illuminate\Support\Carbon::parse($p->window_end)->format('M d, Y H:i') }}
                                </div>
                            </div>
                        </div>

                        @if($p->notes)
                            <div class="pickup-notes">
                                <div class="notes-label">Notes</div>
                                <div class="notes-content">{{ $p->notes }}</div>
                            </div>
                        @endif

                        <div class="pickup-actions">
                    @if(optional(auth()->user()->role)->value === 'courier')
                                <a href="{{ route('deliveries.createFromPickup', $p) }}" class="btn-action btn-select">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    Select Delivery
                                </a>
                    @else
                                <a href="{{ route('pickups.show', $p) }}" class="btn-action btn-view">
                                    <i class="fa-solid fa-eye"></i>
                                    View Details
                                </a>
                    @endif
                            
                            <!-- Bouton Chat pour chaque pickup -->
                            <a href="{{ route('chat.index', ['pickup_id' => $p->id]) }}" class="btn-action btn-chat">
                                <i class="fa-solid fa-comments"></i>
                                Chat
                            </a>
                        </div>
                    </div>
          @endforeach
    </div>

            <div class="mt-4 d-flex justify-content-center">
      {{ $pickups->links() }}
    </div>
  @endif
    </div>
</div>

<script>
// Fonction pour effacer tous les filtres
function clearAllFilters() {
    // Effacer tous les champs de filtre
    document.getElementById('search').value = '';
    document.getElementById('status').value = '';
    
    // Soumettre le formulaire pour recharger la page
    document.getElementById('filters-form').submit();
}

// Auto-submit du formulaire quand on change certains filtres
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    
    // Auto-submit quand on change le statut
    statusSelect.addEventListener('change', function() {
        document.getElementById('filters-form').submit();
    });
    
    
    // Afficher le nombre de résultats filtrés
    const resultsCount = {{ $pickups->total() }};
    const hasFilters = {{ request()->hasAny(['search', 'status']) ? 'true' : 'false' }};
    
    if (hasFilters && resultsCount > 0) {
        const filterInfo = document.createElement('div');
        filterInfo.className = 'alert alert-info mt-3';
        filterInfo.innerHTML = `
            <i class="fa-solid fa-info-circle"></i>
            <strong>Filtres actifs :</strong> ${resultsCount} pickup(s) trouvé(s) avec les critères sélectionnés.
        `;
        document.querySelector('.filters-section').appendChild(filterInfo);
    }
});
</script>

@endsection
