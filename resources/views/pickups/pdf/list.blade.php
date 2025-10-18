<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #059669;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #059669;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #059669;
        }
        
        .summary p {
            margin: 5px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #059669;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-scheduled { background-color: #ffc107; color: #000; }
        .status-assigned { background-color: #17a2b8; color: #fff; }
        .status-in_transit { background-color: #fd7e14; color: #fff; }
        .status-picked { background-color: #28a745; color: #fff; }
        .status-failed { background-color: #dc3545; color: #fff; }
        .status-cancelled { background-color: #6c757d; color: #fff; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚚 {{ $title }}</h1>
        <p>Généré le {{ $generatedAt }}</p>
        <p>Système ReCircle - Gestion des Pickups</p>
    </div>
    
    <div class="summary">
        <h3>📊 Résumé</h3>
        <p><strong>Total des pickups :</strong> {{ $totalCount }}</p>
        <p><strong>Date de génération :</strong> {{ $generatedAt }}</p>
        <p><strong>Système :</strong> ReCircle Waste Management</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 10%;">Code</th>
                <th style="width: 20%;">Adresse</th>
                <th style="width: 15%;">Produit</th>
                <th style="width: 12%;">Générateur</th>
                <th style="width: 10%;">Courrier</th>
                <th style="width: 8%;">Statut</th>
                <th style="width: 10%;">Fenêtre</th>
                <th style="width: 10%;">Créé</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pickups as $pickup)
            <tr>
                <td style="text-align: center;">{{ $pickup->id }}</td>
                <td style="text-align: center;"><strong>{{ $pickup->tracking_code }}</strong></td>
                <td>{{ $pickup->pickup_address }}</td>
                <td>{{ $pickup->wasteItem->title ?? 'N/A' }}</td>
                <td>
                    <strong>{{ $pickup->wasteItem->generator->name ?? 'N/A' }}</strong><br>
                    <small>{{ $pickup->wasteItem->generator->email ?? 'N/A' }}</small>
                </td>
                <td>
                    @if($pickup->courier)
                        <strong>{{ $pickup->courier->name }}</strong><br>
                        <small>{{ $pickup->courier->email }}</small>
                    @else
                        <em>Non assigné</em>
                    @endif
                </td>
                <td style="text-align: center;">
                    <span class="status status-{{ $pickup->status }}">
                        {{ ucfirst($pickup->status) }}
                    </span>
                </td>
                <td style="text-align: center;">
                    @if($pickup->scheduled_pickup_window_start)
                        <strong>{{ $pickup->scheduled_pickup_window_start->format('d/m H:i') }}</strong>
                        @if($pickup->scheduled_pickup_window_end)
                            <br><small>à {{ $pickup->scheduled_pickup_window_end->format('H:i') }}</small>
                        @endif
                    @else
                        <em>Non planifié</em>
                    @endif
                </td>
                <td style="text-align: center;">
                    {{ $pickup->created_at->format('d/m/Y') }}<br>
                    <small>{{ $pickup->created_at->format('H:i') }}</small>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #666;">
                    <em>Aucun pickup trouvé</em>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Document généré automatiquement par le système ReCircle</p>
        <p>Pour toute question, contactez l'administrateur du système</p>
    </div>
</body>
</html>
