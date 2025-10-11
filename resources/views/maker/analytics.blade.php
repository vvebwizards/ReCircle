@extends('layouts.app')

@push('head')
@vite(['resources/css/maker_stats.css'])
@endpush

@section('content')
<main class="stats-dashboard">
    <div class="container">
        <div class="stats-header">
            <div class="header-content">
                <h1><i class="fa-solid fa-chart-line"></i> Maker Analytics</h1>
                <p>Comprehensive insights into your materials, products, and sustainability impact</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="stats-summary-grid">
            <div class="summary-card sustainability">
                <div class="summary-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <div class="summary-content">
                    <h3>{{ number_format($stats['co2_saved'], 2) }} kg</h3>
                    <p>CO₂ Saved</p>
                    <span class="trend">Total environmental impact</span>
                </div>
            </div>

            <div class="summary-card impact">
                <div class="summary-icon">
                    <i class="fa-solid fa-recycle"></i>
                </div>
                <div class="summary-content">
                    <h3>{{ number_format($stats['landfill_avoided'], 2) }} kg</h3>
                    <p>Landfill Avoided</p>
                    <span class="trend">Waste diverted from landfills</span>
                </div>
            </div>

            <div class="summary-card products">
                <div class="summary-icon">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <div class="summary-content">
                    <h3>{{ $stats['total_products'] }}</h3>
                    <p>Total Products</p>
                    <span class="trend">{{ $stats['monthly_products'] }} new this month</span>
                </div>
            </div>

            <div class="summary-card materials">
                <div class="summary-icon">
                    <i class="fa-solid fa-list"></i>
                </div>
                <div class="summary-content">
                    <h3>{{ $stats['total_materials'] }}</h3>
                    <p>Materials Created</p>
                    <span class="trend">{{ number_format($stats['material_stock'], 1) }} total stock</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fa-solid fa-chart-pie"></i> Product Status</h3>
                        <div class="chart-legend">
                            <span class="legend-item published"><i class="fa-solid fa-circle"></i> Published</span>
                            <span class="legend-item draft"><i class="fa-solid fa-circle"></i> Draft</span>
                            <span class="legend-item sold-out"><i class="fa-solid fa-circle"></i> Sold Out</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        @php
                            $totalProducts = $stats['total_products'];
                            $publishedPercentage = $totalProducts > 0 ? ($stats['published_products'] / $totalProducts) * 100 : 0;
                            $draftPercentage = $totalProducts > 0 ? ($stats['draft_products'] / $totalProducts) * 100 : 0;
                            $soldOutPercentage = $totalProducts > 0 ? ($stats['sold_out_products'] / $totalProducts) * 100 : 0;
                        @endphp
                        
                        <div class="pie-chart">
                            <div class="pie-segment published" style="--percentage: {{ $publishedPercentage }}; --color: #10b981;"></div>
                            <div class="pie-segment draft" style="--percentage: {{ $draftPercentage }}; --color: #f59e0b;"></div>
                            <div class="pie-segment sold-out" style="--percentage: {{ $soldOutPercentage }}; --color: #ef4444;"></div>
                            <div class="pie-center">
                                <span class="pie-value">{{ $stats['total_products'] }}</span>
                                <span class="pie-label">Products</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ $stats['published_products'] }}</span>
                            <span class="stat-label">Published</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $stats['draft_products'] }}</span>
                            <span class="stat-label">Drafts</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $stats['sold_out_products'] }}</span>
                            <span class="stat-label">Sold Out</span>
                        </div>
                    </div>
                </div>

          <div class="chart-card">
    <div class="chart-header">
        <h3><i class="fa-solid fa-chart-bar"></i> Material Categories</h3>
    </div>
   <div class="chart-card">
    <div class="chart-header">
        <h3><i class="fa-solid fa-chart-bar"></i> Material Categories</h3>
    </div>
    <div class="chart-container">
        @php
            $categoryColors = [
                'wood' => '#8B4513',
                'metal' => '#C0C0C0', 
                'plastic' => '#3b82f6',
                'textile' => '#ec4899',
                'electronic' => '#f59e0b',
                'glass' => '#10b981',
                'paper' => '#6b7280'
            ];
            
            $totalMaterials = $stats['total_materials'];
            $materialCategories = $stats['material_categories'];
            $availableCategories = ['wood', 'metal', 'plastic', 'textile', 'electronic', 'glass', 'paper'];
        @endphp

        @if($totalMaterials > 0)
            <div class="bar-chart">
                @foreach($availableCategories as $category)
                    @if(isset($materialCategories[$category]))
                        @php
                            $count = $materialCategories[$category];
                            $percentage = $totalMaterials > 0 ? ($count / $totalMaterials) * 100 : 0;
                            $color = $categoryColors[$category] ?? '#3b82f6';
                        @endphp
                        <div class="bar-item">
                            <span class="bar-label">{{ ucfirst($category) }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="--percentage: {{ $percentage }}; --color: {{ $color }};"></div>
                            </div>
                            <span class="bar-value">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="no-data">
                <i class="fa-solid fa-inbox"></i>
                <p>No materials created yet</p>
            </div>
        @endif
    </div>
</div>
</div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fa-solid fa-trash-arrow-up"></i> Waste Items Conversion</h3>
                </div>
                <div class="chart-container">
                    <div class="conversion-metrics">
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div class="metric-content">
                                <h4>{{ $stats['waste_items_purchased'] }}</h4>
                                <p>Waste Items Purchased</p>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fa-solid fa-gears"></i>
                            </div>
                            <div class="metric-content">
                                <h4>{{ $stats['waste_items_converted'] }}</h4>
                                <p>Converted to Materials</p>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-icon">
                                <i class="fa-solid fa-percentage"></i>
                            </div>
                            <div class="metric-content">
                                <h4>53%</h4>
                                <p>Conversion Rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="chart-note">
                        <small><i>Waste item tracking coming soon</i></small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection