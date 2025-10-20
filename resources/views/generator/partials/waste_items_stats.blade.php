<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fa-solid fa-box-archive"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-number">{{ $total }}</h3>
            <p class="stat-label">Total Items</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fa-solid fa-weight-scale"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-number">{{ number_format($avgWeight,2) }}</h3>
            <p class="stat-label">Avg Weight (kg)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fa-solid fa-list-check"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-number">{{ $conditionsCount }}</h3>
            <p class="stat-label">Conditions</p>
        </div>
    </div>
</div>
