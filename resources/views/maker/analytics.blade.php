@extends('layouts.app')

@push('head')
@vite(['resources/css/maker_stats.css'])
<style>
    .monthly-bars-chart {
        display: flex;
        gap: 15px;
        height: 200px;
        margin: 20px 0;
        position: relative;
    }

    .chart-bars {
        display: flex;
        justify-content: space-between;
        align-items: end;
        flex: 1;
        gap: 12px;
        padding: 0 10px;
    }

    .month-column {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        height: 100%;
    }

    .bars-container {
        display: flex;
        gap: 6px;
        align-items: end;
        height: 160px;
        width: 100%;
        justify-content: center;
        position: relative;
    }

    .monthly-bar {
        border-radius: 4px 4px 0 0;
        position: relative;
        min-height: 20px;
        width: 22px;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 4px;
    }

    .monthly-bar:hover {
        transform: translateY(-2px);
        opacity: 0.9;
    }

    .monthly-bar:hover .bar-value {
        opacity: 1;
    }

    .product-bar {
        background: linear-gradient(to top, #10b981, #34d399);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }

    .material-bar {
        background: linear-gradient(to top, #3b82f6, #60a5fa);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .bar-value {
        font-size: 10px;
        font-weight: 600;
        color: white;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .month-label {
        margin-top: 8px;
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    /* Axe Y */
    .y-axis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 160px;
        padding-right: 10px;
        border-right: 1px solid #e5e7eb;
        margin-right: 10px;
        min-width: 30px;
    }

    .y-axis span {
        font-size: 10px;
        color: #9ca3af;
        text-align: right;
        font-weight: 500;
    }

    /* Légende en bas */
    .chart-legend-bottom {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .legend-item.products i {
        color: #10b981;
    }

    .legend-item.materials i {
        color: #3b82f6;
    }

    /* Style pour no-data */
    .no-data {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        text-align: center;
        color: #6b7280;
        min-height: 200px;
    }

    .no-data i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #d1d5db;
    }

    .no-data p {
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .no-data small {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .monthly-bars-chart {
            height: 180px;
            gap: 10px;
        }
        
        .chart-bars {
            gap: 8px;
        }
        
        .bars-container {
            height: 140px;
        }
        
        .monthly-bar {
            width: 18px;
        }
        
        .y-axis {
            height: 140px;
        }
        
        .y-axis span {
            font-size: 9px;
        }
    }

    /* Animation pour les barres */
    @keyframes barGrowth {
        from {
            height: 0%;
        }
        to {
            height: var(--final-height);
        }
    }

    .monthly-bar {
        animation: barGrowth 0.8s ease-out;
    }
</style>
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
                <a href="{{ route('maker.dashboard') }}" class="btn btn-outline">
                    <i class="fa-solid fa-chevron-left"></i>
                    <span>Back to Dashboard</span>
                </a>
                <a href="{{ route('analytics.pdf') }}" class="btn btn-primary" target="_blank">
                    <i class="fa-solid fa-download"></i>
                    <span>Export Analytics Report</span>
                </a>
            </div>
        </div>

        <div class="stats-summary-grid">
            <!-- Vos cartes de résumé existantes -->
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
                    </div>
                </div>
                <div class="chart-container">
                    @php
                        $totalProducts = $stats['total_products'];
                        $publishedPercentage = $totalProducts > 0 ? ($stats['published_products'] / $totalProducts) * 100 : 0;
                        $draftPercentage = $totalProducts > 0 ? ($stats['draft_products'] / $totalProducts) * 100 : 0;
                    @endphp
                    
                    <div class="pie-chart">
                        <div class="pie-segment published" style="--percentage: {{ $publishedPercentage }}; --color: #10b981;"></div>
                        <div class="pie-segment draft" style="--percentage: {{ $draftPercentage }}; --color: #f59e0b;"></div>
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
                </div>
            </div>

            <!-- Graphique Material Categories -->
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

            <!-- Monthly Progress - Version CORRIGÉE -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fa-solid fa-chart-line"></i> Monthly Progress</h3>
                    <div class="chart-legend">
                        <span class="legend-item products"><i class="fa-solid fa-circle"></i> Products</span>
                        <span class="legend-item materials"><i class="fa-solid fa-circle"></i> Materials</span>
                    </div>
                </div>
                <div class="chart-container">
                    @php
                        // Debug: Vérifier les données
                        $monthlyData = $stats['monthly_comparison'] ?? [];
                        
                        // Si pas de données, créer des données de démonstration
                        if (empty($monthlyData)) {
                            $monthlyData = [
                                ['name' => 'Jan', 'products' => 5, 'materials' => 8],
                                ['name' => 'Feb', 'products' => 8, 'materials' => 12],
                                ['name' => 'Mar', 'products' => 12, 'materials' => 15],
                                ['name' => 'Apr', 'products' => 7, 'materials' => 10],
                                ['name' => 'May', 'products' => 15, 'materials' => 18],
                                ['name' => 'Jun', 'products' => 10, 'materials' => 14],
                            ];
                        }
                        
                        $hasProductsData = !empty($monthlyData) && max(array_column($monthlyData, 'products')) > 0;
                        $hasMaterialsData = !empty($monthlyData) && max(array_column($monthlyData, 'materials')) > 0;
                        $hasMonthlyData = $hasProductsData || $hasMaterialsData;
                        
                        // Calculer la valeur maximale pour l'échelle
                        $maxValue = 0;
                        if (!empty($monthlyData)) {
                            $maxProducts = max(array_column($monthlyData, 'products'));
                            $maxMaterials = max(array_column($monthlyData, 'materials'));
                            $maxValue = max($maxProducts, $maxMaterials);
                            // S'assurer que maxValue n'est pas 0
                            $maxValue = $maxValue > 0 ? $maxValue : 20;
                        }
                    @endphp
                    
                    @if($hasMonthlyData)
                        <div class="monthly-bars-chart">
                            <!-- Axe Y -->
                            <div class="y-axis">
                                <span>{{ $maxValue }}</span>
                                <span>{{ round($maxValue * 0.75) }}</span>
                                <span>{{ round($maxValue * 0.5) }}</span>
                                <span>{{ round($maxValue * 0.25) }}</span>
                                <span>0</span>
                            </div>
                            
                            <!-- Barres -->
                            <div class="chart-bars">
                                @foreach($monthlyData as $monthKey => $monthData)
                                    @php
                                        $productsHeight = $maxValue > 0 ? ($monthData['products'] / $maxValue) * 100 : 0;
                                        $materialsHeight = $maxValue > 0 ? ($monthData['materials'] / $maxValue) * 100 : 0;
                                    @endphp
                                    <div class="month-column">
                                        <div class="bars-container">
                                            @if($hasProductsData)
                                                <div class="monthly-bar product-bar" style="height: {{ $productsHeight }}%">
                                                    <span class="bar-value">{{ $monthData['products'] }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($hasMaterialsData)
                                                <div class="monthly-bar material-bar" style="height: {{ $materialsHeight }}%">
                                                    <span class="bar-value">{{ $monthData['materials'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="month-label">{{ $monthData['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Légende -->
                        <div class="chart-legend-bottom">
                            @if($hasProductsData && $hasMaterialsData)
                                <span class="legend-item products"><i class="fa-solid fa-circle"></i> Products Created</span>
                                <span class="legend-item materials"><i class="fa-solid fa-circle"></i> Materials Created</span>
                            @elseif($hasProductsData)
                                <span class="legend-item products"><i class="fa-solid fa-circle"></i> Products Created</span>
                            @elseif($hasMaterialsData)
                                <span class="legend-item materials"><i class="fa-solid fa-circle"></i> Materials Created</span>
                            @endif
                        </div>
                        
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-calendar-plus"></i>
                            <p>No monthly activity yet</p>
                            <small>Create products or materials to see your progress over time</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Les autres graphiques -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fa-solid fa-leaf"></i> CO2 Savings by Category</h3>
                </div>
                <div class="chart-container">
                    @if(!empty($stats['co2_by_category']) && $stats['co2_saved'] > 0)
                        <div class="co2-chart">
                            @foreach($stats['co2_by_category'] as $category => $co2Saved)
                                @if($co2Saved > 0)
                                    <div class="co2-item">
                                        <span class="co2-label">{{ ucfirst($category) }}</span>
                                        <div class="co2-track">
                                            <div class="co2-fill" style="--percentage: {{ ($co2Saved / max($stats['co2_saved'], 1)) * 100 }}%;"></div>
                                        </div>
                                        <span class="co2-value">{{ number_format($co2Saved, 1) }} kg</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-leaf"></i>
                            <p>No CO2 savings calculated yet</p>
                            <small>CO2 impact is calculated when you create products using sustainable materials</small>
                        </div>
                    @endif
                </div>
            </div>

           
        </div>
    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Vérifier que les barres sont bien rendues
    const bars = document.querySelectorAll('.monthly-bar');
    console.log('Monthly bars found:', bars.length);
    
    bars.forEach((bar, index) => {
        const height = bar.style.height;
        console.log(`Bar ${index}: height = ${height}`);
    });
});
</script>
@endpush

@endsection