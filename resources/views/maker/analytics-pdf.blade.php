<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report - {{ $user->name }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 25px; 
            color: #2d3748; 
            line-height: 1.6;
            font-size: 12px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #10b981; 
            padding-bottom: 20px; 
        }
        .section { 
            margin-bottom: 30px; 
            break-inside: avoid; 
        }
        .section-title {
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 12px; 
            margin-bottom: 25px; 
        }
        .stat-card { 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            padding: 15px; 
            text-align: center;
        }
        .stat-value { 
            font-size: 18px; 
            font-weight: bold; 
            color: #10b981; 
            margin: 8px 0; 
        }
        .stat-value.empty { 
            color: #a0aec0; 
        }
        .stat-label { 
            color: #718096; 
            font-size: 11px;
            text-transform: uppercase;
        }
        .chart-container {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border: 1px solid #e2e8f0;
        }
        .bar-chart {
            display: flex;
            align-items: end;
            height: 120px;
            gap: 8px;
            padding: 10px 0;
        }
        .bar {
            flex: 1;
            background: linear-gradient(to top, #10b981, #34d399);
            border-radius: 4px 4px 0 0;
            position: relative;
            min-height: 20px;
        }
        .bar-label {
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #718096;
        }
        .bar-value {
            position: absolute;
            top: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #2d3748;
        }
        
        /* Monthly comparison chart styles */
        .monthly-bars-chart {
            display: flex;
            gap: 15px;
            height: 180px;
            margin: 20px 0;
        }
        .chart-bars {
            display: flex;
            justify-content: space-between;
            align-items: end;
            flex: 1;
            gap: 12px;
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
            gap: 3px;
            align-items: end;
            height: 140px;
            width: 100%;
            justify-content: center;
        }
        .monthly-bar {
            border-radius: 3px 3px 0 0;
            position: relative;
            min-height: 15px;
            width: 16px;
        }
        .product-bar {
            background: linear-gradient(to top, #10b981, #34d399);
        }
        .material-bar {
            background: linear-gradient(to top, #3b82f6, #60a5fa);
        }
        .month-label {
            margin-top: 8px;
            font-size: 10px;
            color: #6b7280;
            font-weight: 500;
            text-align: center;
        }
        .y-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 140px;
            padding-right: 8px;
            border-right: 1px solid #e5e7eb;
            margin-right: 8px;
        }
        .y-axis span {
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #a0aec0;
            background: #f7fafc;
            border-radius: 6px;
            margin: 15px 0;
        }
        .empty-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .insight-box {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fffaf0;
            border: 1px solid #fbd38d;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            color: #744210;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }
        .data-table th {
            background: #f7fafc;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            color: #718096; 
            font-size: 10px; 
            border-top: 1px solid #e2e8f0; 
            padding-top: 15px; 
        }
        .description {
            margin: 10px 0;
            color: #4a5568;
            line-height: 1.5;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 8px;
            background: #f7fafc;
            border-radius: 4px;
        }
        .get-started-box {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 6px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }
        .chart-legend-bottom {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #6b7280;
        }
        .legend-item.products i {
            color: #10b981;
        }
        .legend-item.materials i {
            color: #3b82f6;
        }
        .section-description {
            background: #f8fafc;
            border-left: 3px solid #10b981;
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 0 4px 4px 0;
            font-size: 11px;
            color: #4a5568;
        }
        .stat-subtitle {
            font-size: 9px; 
            color: #718096; 
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0 0 5px 0; color: #2d3748; font-size: 20px;">Sustainability Analytics Report</h1>
        <p style="color: #718096; margin: 0; font-size: 12px;">{{ $user->company_name ?? $user->name }} • Generated on {{ $generation_date }}</p>
    </div>

    <div class="section">
        <h2 class="section-title">Executive Summary</h2>
        <div class="section-description">
            This executive summary provides a high-level overview of your sustainability performance, 
            highlighting key metrics and achievements. It serves as a quick reference for understanding 
            your environmental impact and progress toward circular economy goals.
        </div>
        
        <div class="description">
            @if($stats['total_products'] == 0 && $stats['total_materials'] == 0)
                Welcome to your analytics dashboard! This is the beginning of your sustainability journey. 
                Start by creating materials and products to track your environmental impact. This platform 
                helps you measure and optimize your contribution to the circular economy through upcycling 
                and waste reduction initiatives.
            @else
                This report provides a comprehensive overview of your sustainable manufacturing performance, 
                highlighting key environmental impacts and production metrics. Your efforts in upcycling and 
                waste reduction are creating measurable positive impacts on the environment while building 
                economic value from materials that would otherwise be discarded.
            @endif
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value {{ $stats['co2_saved'] == 0 ? 'empty' : '' }}">
                    {{ $stats['co2_saved'] > 0 ? number_format($stats['co2_saved'], 1) . ' kg' : 'No Data' }}
                </div>
                <div class="stat-label">CO₂ Emissions Saved</div>
                <div class="stat-subtitle">
                    Equivalent to {{ $stats['co2_saved'] > 0 ? round($stats['co2_saved'] / 21.77) : 0 }} trees planted
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value {{ $stats['landfill_avoided'] == 0 ? 'empty' : '' }}">
                    {{ $stats['landfill_avoided'] > 0 ? number_format($stats['landfill_avoided'], 1) . ' kg' : 'No Data' }}
                </div>
                <div class="stat-label">Landfill Avoided</div>
                <div class="stat-subtitle">
                    Waste diverted from landfills
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value {{ $stats['total_products'] == 0 ? 'empty' : '' }}">
                    {{ $stats['total_products'] > 0 ? $stats['total_products'] : 'No Products' }}
                </div>
                <div class="stat-label">Products Created</div>
                <div class="stat-subtitle">
                    {{ $stats['published_products'] ?? 0 }} published, {{ $stats['draft_products'] ?? 0 }} drafts
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-value {{ $stats['total_materials'] == 0 ? 'empty' : '' }}">
                    {{ $stats['total_materials'] > 0 ? $stats['total_materials'] : 'No Materials' }}
                </div>
                <div class="stat-label">Materials Created</div>
                <div class="stat-subtitle">
                    From {{ $stats['waste_items_converted'] ?? 0 }} waste items
                </div>
            </div>
        </div>

        @if($stats['total_products'] == 0 && $stats['total_materials'] == 0)
        <div class="get-started-box">
            <div style="font-weight: bold; color: #2b6cb0; margin-bottom: 10px;">Ready to Get Started?</div>
            <div class="description">
                To begin tracking your sustainability impact:<br>
                1. Create your first material from waste items<br>
                2. Design products using your materials<br>
                3. Track your environmental impact here<br>
                4. Monitor progress and optimize your processes
            </div>
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Environmental Impact Analysis</h2>
        <div class="section-description">
            This section quantifies the environmental benefits of your upcycling activities, including 
            CO₂ emissions reduction and landfill diversion. These metrics help demonstrate the tangible 
            environmental value created through your sustainable manufacturing practices.
        </div>
        
        @if($stats['co2_saved'] == 0 && $stats['landfill_avoided'] == 0)
        <div class="empty-state">
            <div class="empty-icon">🌱</div>
            <div style="font-weight: bold; margin-bottom: 8px;">No Environmental Impact Data Yet</div>
            <div class="description">
                Your environmental impact will be calculated once you create products using sustainable materials.<br>
                Start by converting waste items into materials and creating products with them. As you build your 
                product portfolio, this section will automatically populate with data showing your positive 
                environmental contributions.
            </div>
        </div>
        @else
        <div class="description">
            Your sustainable practices have resulted in substantial environmental benefits through upcycling.
            The metrics below demonstrate how your manufacturing approach reduces environmental impact compared
            to traditional production methods that rely on virgin materials.
        </div>

        @if(!empty($stats['co2_by_category']) && max($stats['co2_by_category']) > 0)
        <div class="chart-container">
            <div style="text-align: center; margin-bottom: 10px; font-weight: bold; color: #2d3748;">
                CO₂ Savings by Material Category
            </div>
            <div style="text-align: center; margin-bottom: 15px; font-size: 10px; color: #718096;">
                This chart shows which material categories contribute most to your CO₂ savings, helping 
                identify your most impactful upcycling activities.
            </div>
            <div class="bar-chart">
                @foreach($stats['co2_by_category'] as $category => $co2Saved)
                @php
                    $maxCo2 = max($stats['co2_by_category']);
                    $height = $maxCo2 > 0 ? ($co2Saved / $maxCo2) * 100 : 0;
                @endphp
                <div class="bar" style="height: {{ $height }}%;">
                    <div class="bar-value">{{ number_format($co2Saved, 1) }}kg</div>
                    <div class="bar-label">{{ substr(ucfirst($category), 0, 8) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="metric-row">
            <span>Total Waste Diverted:</span>
            <span style="font-weight: bold; {{ $stats['landfill_avoided'] > 0 ? 'color: #10b981;' : 'color: #a0aec0;' }}">
                {{ $stats['landfill_avoided'] > 0 ? number_format($stats['landfill_avoided'], 2) . ' kg' : 'Not yet measured' }}
            </span>
        </div>
        <div class="description" style="font-size: 10px; color: #718096; margin-top: -5px; margin-bottom: 15px;">
            This represents the total weight of materials that have been diverted from landfills through your upcycling efforts.
        </div>
        
        <div class="metric-row">
            <span>Equivalent Trees Planted:</span>
            <span style="font-weight: bold; {{ $stats['co2_saved'] > 0 ? 'color: #10b981;' : 'color: #a0aec0;' }}">
                {{ $stats['co2_saved'] > 0 ? round($stats['co2_saved'] / 21.77) . ' trees' : 'Calculate with CO₂ data' }}
            </span>
        </div>
        <div class="description" style="font-size: 10px; color: #718096; margin-top: -5px;">
            Based on EPA calculations that one mature tree absorbs approximately 21.77 kg of CO₂ per year.
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Product Portfolio</h2>
        <div class="section-description">
            This section analyzes your product creation activities, showing the distribution between 
            published and draft products. Understanding your product pipeline helps optimize your 
            manufacturing and marketing strategies.
        </div>
        
        @if($stats['total_products'] == 0)
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <div style="font-weight: bold; margin-bottom: 8px;">No Products Created Yet</div>
            <div class="description">
                Product metrics will appear here once you create and publish products.<br>
                Start by designing your first upcycled product. Consider beginning with simple designs 
                that showcase the unique characteristics of your upcycled materials while meeting 
                market demand.
            </div>
        </div>
        @else
        <div class="description">
            Overview of your product creation and publication status. A healthy product portfolio 
            typically maintains a balance between published products (generating value) and draft 
            products (future pipeline).
        </div>

        <div class="chart-container">
            <div style="text-align: center; margin-bottom: 10px; font-weight: bold; color: #2d3748;">
                Product Status Distribution
            </div>
            <div style="text-align: center; margin-bottom: 15px; font-size: 10px; color: #718096;">
                This visualization shows the balance between published products (market-ready) and 
                draft products (in development).
            </div>
            <div class="bar-chart">
                @php
                    $statuses = [
                        'Published' => $stats['published_products'],
                        'Draft' => $stats['draft_products']
                    ];
                    $maxStatus = max($statuses);
                @endphp
                @foreach($statuses as $status => $count)
                @if($count > 0)
                @php
                    $height = $maxStatus > 0 ? ($count / $maxStatus) * 100 : 0;
                    $colors = [
                        'Published' => '#10b981',
                        'Draft' => '#f59e0b'
                    ];
                @endphp
                <div class="bar" style="height: {{ $height }}%; background: linear-gradient(to top, {{ $colors[$status] }}, {{ $colors[$status] }}99);">
                    <div class="bar-value">{{ $count }}</div>
                    <div class="bar-label">{{ $status }}</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Performance Metric</th>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Active Listings</td>
                    <td style="font-weight: bold; {{ $stats['published_products'] > 0 ? 'color: #10b981;' : 'color: #a0aec0;' }}">
                        {{ $stats['published_products'] > 0 ? $stats['published_products'] : 'No active listings' }}
                    </td>
                    <td>Products currently published and available to customers</td>
                </tr>
                <tr>
                    <td>Draft Products</td>
                    <td style="font-weight: bold; {{ $stats['draft_products'] > 0 ? 'color: #f59e0b;' : 'color: #a0aec0;' }}">
                        {{ $stats['draft_products'] > 0 ? $stats['draft_products'] : 'No drafts' }}
                    </td>
                    <td>Products in development or awaiting publication</td>
                </tr>
                <tr>
                    <td>Monthly Production</td>
                    <td style="font-weight: bold; {{ $stats['monthly_products'] > 0 ? 'color: #10b981;' : 'color: #a0aec0;' }}">
                        {{ $stats['monthly_products'] > 0 ? $stats['monthly_products'] : 'None this month' }}
                    </td>
                    <td>New products created this month, indicating recent activity levels</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Material Portfolio Analysis</h2>
        <div class="section-description">
            This section examines your material inventory, showing the diversity of upcycled materials 
            in your portfolio. A diverse material base supports product innovation and resilience to 
            supply chain fluctuations.
        </div>
        
        @if($stats['total_materials'] == 0)
        <div class="empty-state">
            <div class="empty-icon">🛠️</div>
            <div style="font-weight: bold; margin-bottom: 8px;">No Materials Created Yet</div>
            <div class="description">
                Your material portfolio is empty. Start by creating materials from waste items<br>
                to build your sustainable manufacturing foundation. Consider beginning with readily 
                available waste streams that align with your production capabilities and market interests.
            </div>
        </div>
        @else
        <div class="description">
            Your material diversity demonstrates your approach to upcycling. A varied material portfolio 
            enables product differentiation and reduces dependency on specific waste streams while 
            maximizing environmental impact.
        </div>

        @if(!empty($stats['material_categories']) && max($stats['material_categories']) > 0)
        <div class="chart-container">
            <div style="text-align: center; margin-bottom: 10px; font-weight: bold; color: #2d3748;">
                Material Categories Distribution
            </div>
            <div style="text-align: center; margin-bottom: 15px; font-size: 10px; color: #718096;">
                This chart shows the distribution of materials across different categories, highlighting 
                your areas of material specialization and potential diversification opportunities.
            </div>
            <div class="bar-chart">
                @foreach($stats['material_categories'] as $category => $count)
                @if($count > 0)
                @php
                    $maxCount = max($stats['material_categories']);
                    $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                @endphp
                <div class="bar" style="height: {{ $height }}%;">
                    <div class="bar-value">{{ $count }}</div>
                    <div class="bar-label">{{ substr(ucfirst($category), 0, 6) }}</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="description">
            <strong>Portfolio Insights:</strong> Your material library contains {{ $stats['total_materials'] }} 
            items across {{ count($stats['material_categories']) }} different categories, with a total stock 
            quantity of {{ number_format($stats['material_stock'], 1) }} units. This diversity provides 
            flexibility in product design and helps mitigate supply chain risks associated with 
            dependency on single material types.
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Strategic Insights & Recommendations</h2>
        <div class="section-description">
            This final section provides actionable insights and recommendations based on your current 
            sustainability performance. Use these insights to guide your strategic decisions and 
            prioritize future initiatives.
        </div>
        
        @if($stats['total_products'] == 0 && $stats['total_materials'] == 0)
        <div class="warning-box">
            <div style="font-weight: bold; color: #744210; margin-bottom: 10px;">Getting Started Guide</div>
            <div class="description">
                • Begin by creating materials from available waste items - focus on materials that align with your skills and equipment<br>
                • Design products that showcase your unique upcycling style while meeting market needs<br>
                • Publish products to showcase your work and start generating value from your upcycling efforts<br>
                • Monitor this analytics dashboard to track your progress and identify optimization opportunities<br>
                • Consider joining sustainability networks to learn from other upcycling businesses
            </div>
        </div>
        @else
        <div class="insight-box">
            <div style="font-weight: bold; color: #059669; margin-bottom: 10px;">Key Achievements</div>
            <div class="description">
                @if($stats['landfill_avoided'] > 0)
                • Diverted {{ number_format($stats['landfill_avoided'], 2) }} kg from landfills - equivalent to the weight of {{ round($stats['landfill_avoided'] / 1.5) }} car tires<br>
                @endif
                @if($stats['total_materials'] > 0)
                • Created {{ $stats['total_materials'] }} sustainable material variations, demonstrating material innovation<br>
                @endif
                @if($stats['total_products'] > 0)
                • Designed {{ $stats['total_products'] }} upcycled products, showcasing creative reuse of materials<br>
                @endif
                @if($stats['co2_saved'] > 0)
                • Saved {{ number_format($stats['co2_saved'], 1) }} kg of CO₂ emissions - equivalent to {{ round($stats['co2_saved'] / 0.4) }} km not driven by a car<br>
                @endif
                @if($stats['waste_items_converted'] > 0)
                • Converted {{ $stats['waste_items_converted'] }} waste items into valuable resources<br>
                @endif
            </div>
        </div>

        <div class="description">
            <strong>Strategic Recommendations:</strong> 
            @if($stats['total_materials'] == 0)
            Focus on building your material portfolio by converting waste items into usable materials. 
            Consider starting with 2-3 material types that align with your production capabilities and 
            target market preferences.
            @elseif($stats['published_products'] == 0)
            Convert your {{ $stats['total_materials'] }} materials into published products to showcase your work 
            and start generating revenue. Consider creating a product launch plan with clear marketing 
            messaging about your sustainability story.
            @elseif(count($stats['material_categories']) < 3)
            Consider expanding your material categories to increase product diversity and reduce 
            dependency on specific waste streams. Research additional locally available waste materials 
            that could complement your current portfolio.
            @else
            Explore opportunities to scale your impact by optimizing your most successful product lines 
            or expanding into new market segments. Consider conducting customer research to identify 
            additional product opportunities that align with your sustainability mission.
            @endif
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Generated by ReCircle Analytics Platform • Confidential Report for {{ $user->name }}</p>
        <p>Document ID: {{ uniqid() }} • {{ url('/') }}</p>
        <p style="margin-top: 10px; font-size: 9px;">
            This report measures environmental impact based on standard conversion factors and may include estimates. 
            Actual environmental benefits may vary based on specific local conditions and processing methods.
        </p>
    </div>
</body>
</html>