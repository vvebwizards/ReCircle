@extends('layouts.app')

@section('title', 'My Map - ReCircle')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    body {
        background: white;
    }
    
    .map-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
        margin-top: 40px;
        height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
        background: white;
    }
    
    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        margin-bottom: 20px;
        padding: 15px 20px;
        background: linear-gradient(135deg,rgb(255, 255, 255) 0%,rgb(220, 213, 213) 100%);
        border-radius: 12px;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
    }
    
    .map-title {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .map-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .btn-control {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        background: rgba(249, 249, 249, 0.2);
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-control:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    
    .btn-optimize {
        background: linear-gradient(135deg,rgb(86, 100, 94),rgb(205, 212, 211));
        color:rgb(56, 57, 58);
    }
    
    .btn-optimize:hover {
        background: linear-gradient(135deg,rgb(119, 121, 121),rgb(71, 73, 72));
    }
    
    .map-content {
        display: flex;
        gap: 20px;
        flex: 1;
        min-height: 0;
    }
    
    .map-wrapper {
        flex: 2;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    #map {
        width: 100%;
        height: 100%;
        min-height: 500px;
    }
    
    .deliveries-panel {
        flex: 1;
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow-y: auto;
        max-height: calc(100vh - 160px);
        border: 1px solid #e2e8f0;
    }
    
    .panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .delivery-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .delivery-item:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(200, 196, 196, 0.1);
    }
    
    .delivery-item.active {
        background: linear-gradient(135deg,rgb(167, 167, 167),rgb(185, 191, 198));
        color: white;
        border-color: #374151;
    }
    
    .delivery-id {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 5px;
    }
    
    .delivery-address {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 5px;
    }
    
    .delivery-item.active .delivery-address {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .delivery-time {
        font-size: 0.8rem;
        color: #374151;
        font-weight: 600;
    }
    
    .delivery-item.active .delivery-time {
        color: #f3f4f6;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 5px;
    }
    
    .status-assigned {
        background: #fbbf24;
        color: white;
    }
    
    .status-in_transit {
        background: #3b82f6;
        color: white;
    }
    
    .stats-panel {
        background: #f3f4f6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #d1d5db;
    }
    
    .stats-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1e293b;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #64748b;
    }
    
    @media (max-width: 768px) {
        .map-content {
            flex-direction: column;
        }
        
        .deliveries-panel {
            max-height: 300px;
        }
        
        .map-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        .map-controls {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>

<div class="map-container">
    <!-- En-tête de la carte -->
    <div class="map-header">
        <div class="map-title">
            <i class="fa-solid fa-map"></i>
        </div>
        <div class="map-controls">
            <button class="btn-control" onclick="centerOnMe()">
                <i class="fa-solid fa-location-crosshairs"></i>
                 My Position
            </button>
            <button class="btn-control" onclick="toggleNightMode()">
                <i class="fa-solid fa-moon"></i>
                 Night Mode
            </button>
            <button class="btn-control btn-optimize" onclick="optimizeRoute()">
                <i class="fa-solid fa-route"></i>
                 Optimize Route
            </button>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="map-content">
        <!-- Carte -->
        <div class="map-wrapper">
            <div id="map"></div>
        </div>

        <!-- Panneau des livraisons -->
        <div class="deliveries-panel">
            <div class="panel-title">
                <i class="fa-solid fa-list"></i>
                 My Deliveries
            </div>

            <!-- Statistiques -->
            <div class="stats-panel">
                 <div class="stats-title">📊 Daily Statistics</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number" id="total-deliveries">{{ $deliveries->count() }}</div>
                         <div class="stat-label">Deliveries</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="remaining-time">{{ $mapData['stats']['remaining_time_minutes'] + round(($mapData['stats']['total_distance_km'] / 25) * 60) }} min</div>
                         <div class="stat-label">Remaining Time</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="total-distance">{{ $mapData['stats']['total_distance_km'] }} km</div>
                        <div class="stat-label">Distance (km)</div>
                    </div>
                </div>
            </div>

            <!-- Liste des livraisons -->
            <div class="deliveries-list">
                @forelse($deliveries as $delivery)
                    <div class="delivery-item" data-delivery-id="{{ $delivery->id }}" onclick="selectDelivery({{ $delivery->id }})">
                         <div class="delivery-id">🚚 Delivery #{{ $delivery->id }}</div>
                        
                        <!-- Adresse de pickup -->
                        <div style="background: #f3f4f6; padding: 8px; border-radius: 6px; margin: 5px 0; border-left: 3px solid #374151;">
                             <div style="font-size: 0.8rem; color: #1f2937; font-weight: 600; margin-bottom: 2px;">📍 Pickup Address:</div>
                             <div class="delivery-address">{{ $delivery->pickup->pickup_address ?? 'Address not available' }}</div>
                        </div>
                        
                        <!-- Informations produit -->
                        <div style="background: #f3f4f6; padding: 8px; border-radius: 6px; margin: 5px 0; border-left: 3px solid #374151;">
                            <div class="delivery-time">📦 {{ $delivery->pickup->wasteItem->title ?? 'N/A' }}</div>
                             <div class="delivery-time">⏰ {{ $delivery->pickup->scheduled_pickup_window_start ? $delivery->pickup->scheduled_pickup_window_start->format('H:i') : 'Not scheduled' }}</div>
                        </div>
                        
                        <!-- Hub -->
                        <div style="background: #f3f4f6; padding: 8px; border-radius: 6px; margin: 5px 0; border-left: 3px solid #374151;">
                            <div style="font-size: 0.8rem; color: #1f2937; font-weight: 600; margin-bottom: 2px;">🏢 Hub:</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Avenue Habib Bourguiba, Tunis</div>
                        </div>
                        
                        <span class="status-badge status-{{ $delivery->status }}">
                            {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                        </span>
                    </div>
                @empty
                    <div style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                         <p>No deliveries assigned</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Variables globales
let map;
let courierMarker;
let deliveryMarkers = [];
let hubMarker;
let isNightMode = false;
let selectedDelivery = null;

// Fonction pour calculer la distance réelle entre deux points (formule de Haversine)
function calculateRealDistance(lat1, lng1, lat2, lng2) {
    const earthRadius = 6371; // Rayon de la Terre en km
    
    const lat1Rad = lat1 * Math.PI / 180;
    const lng1Rad = lng1 * Math.PI / 180;
    const lat2Rad = lat2 * Math.PI / 180;
    const lng2Rad = lng2 * Math.PI / 180;
    
    const deltaLat = lat2Rad - lat1Rad;
    const deltaLng = lng2Rad - lng1Rad;
    
    const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) + 
              Math.cos(lat1Rad) * Math.cos(lat2Rad) * 
              Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    return earthRadius * c;
}

// Données de la carte
const mapData = @json($mapData);
const hubLocation = mapData.hub;
const deliveryStats = mapData.stats;
// Initialisation de la carte
function initMap() {
    // Créer la carte centrée sur Tunis
    map = L.map('map').setView([36.8065, 10.1815], 11);
    
    // Ajouter les tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Marqueur du hub
    hubMarker = L.marker([hubLocation.lat, hubLocation.lng], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);
    
    hubMarker.bindPopup(`
        <div style="text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #dc2626;">🏢 Hub ReCircle</h3>
            <p style="margin: 5px 0; color: #64748b;">📍 ${hubLocation.address}</p>
            <p style="margin: 5px 0; color: #64748b;">📞 +216 71 123 456</p>
        </div>
    `);
    
    // Géolocalisation du livreur
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const courierLat = position.coords.latitude;
            const courierLng = position.coords.longitude;
            
            courierMarker = L.marker([courierLat, courierLng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            
            courierMarker.bindPopup(`
                <div style="text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #2563eb;">👤 Your Position</h3>
                    <p style="margin: 5px 0; color: #64748b;">📍 ${courierLat.toFixed(6)}, ${courierLng.toFixed(6)}</p>
                    <p style="margin: 5px 0; color: #64748b;">⏰ ${new Date().toLocaleTimeString()}</p>
                </div>
            `);
            
            // Centrer la carte sur le livreur
            map.setView([courierLat, courierLng], 12);
        });
    }
    
    // Ajouter les marqueurs des livraisons
    addDeliveryMarkers();
}

// Ajouter les marqueurs des livraisons
function addDeliveryMarkers() {
    mapData.deliveries.forEach(delivery => {
        // Coordonnées par défaut (centre de Tunis) si pas de coordonnées spécifiques
        const lat = delivery.lat || 36.8065 + (Math.random() - 0.5) * 0.1;
        const lng = delivery.lng || 10.1815 + (Math.random() - 0.5) * 0.1;
        
        const marker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        
        marker.bindPopup(`
            <div style="text-align: center; min-width: 250px;">
                 <h3 style="margin: 0 0 10px 0; color: #059669;">🚚 Delivery #${delivery.id}</h3>
                <div style="background: #f0f9ff; padding: 8px; border-radius: 6px; margin: 8px 0;">
                     <p style="margin: 3px 0; color: #0369a1;"><strong>📍 Pickup Address:</strong></p>
                    <p style="margin: 3px 0; color: #64748b; font-size: 0.9rem;">${delivery.pickup_address}</p>
                </div>
                <div style="background: #f0fdf4; padding: 8px; border-radius: 6px; margin: 8px 0;">
                     <p style="margin: 3px 0; color: #059669;"><strong>📦 Product:</strong> ${delivery.waste_item_title}</p>
                     <p style="margin: 3px 0; color: #64748b;"><strong>⏰ Time:</strong> ${delivery.scheduled_time}</p>
                     <p style="margin: 3px 0; color: #64748b;"><strong>📋 Status:</strong> ${delivery.status}</p>
                </div>
                <div style="background: #fef3c7; padding: 8px; border-radius: 6px; margin: 8px 0;">
                    <p style="margin: 3px 0; color: #92400e;"><strong>🏢 Hub:</strong> ${hubLocation.address}</p>
                </div>
                <div style="margin-top: 10px;">
                    <button onclick="navigateTo(${lat}, ${lng})" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 5px; margin: 2px; cursor: pointer; font-size: 0.8rem;">
                         🧭 Go to Pickup
                    </button>
                    <button onclick="navigateToHub()" style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 5px; margin: 2px; cursor: pointer; font-size: 0.8rem;">
                         🏢 Go to Hub
                    </button>
                </div>
            </div>
        `);
        
        deliveryMarkers.push({
            id: delivery.id,
            marker: marker,
            delivery: delivery
        });
        
        // Événement de clic sur le marqueur
        marker.on('click', function() {
            selectDelivery(delivery.id);
        });
    });
}

// Sélectionner une livraison
function selectDelivery(deliveryId) {
    // Retirer la classe active de tous les éléments
    document.querySelectorAll('.delivery-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Ajouter la classe active à l'élément sélectionné
    const deliveryElement = document.querySelector(`[data-delivery-id="${deliveryId}"]`);
    if (deliveryElement) {
        deliveryElement.classList.add('active');
    }
    
    // Centrer la carte sur la livraison sélectionnée
    const deliveryMarker = deliveryMarkers.find(dm => dm.id === deliveryId);
    if (deliveryMarker) {
        map.setView(deliveryMarker.marker.getLatLng(), 15);
        deliveryMarker.marker.openPopup();
    }
    
    selectedDelivery = deliveryId;
}

// Centrer sur ma position
function centerOnMe() {
    if (courierMarker) {
        map.setView(courierMarker.getLatLng(), 15);
        courierMarker.openPopup();
    } else {
         alert('Position not available. Please enable geolocation.');
    }
}

// Basculer le mode nuit
function toggleNightMode() {
    isNightMode = !isNightMode;
    
    if (isNightMode) {
        // Mode nuit
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO'
        }).addTo(map);
    } else {
        // Mode jour
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }
}

// Optimiser la route
function optimizeRoute() {
    if (deliveryMarkers.length === 0) {
         alert('No deliveries to optimize.');
        return;
    }
    
    // Calculer les nouvelles statistiques basées sur les marqueurs actuels
    const activeDeliveries = deliveryMarkers.length;
    
    if (activeDeliveries === 0) {
        document.getElementById('total-distance').textContent = '0 km';
        document.getElementById('remaining-time').textContent = '0 min';
        return;
    }
    
    // Calculer la distance réelle entre tous les points
    let totalDistance = 0;
    const hubLat = hubLocation.lat;
    const hubLng = hubLocation.lng;
    
    // Coordonnées de tous les pickups
    const pickupCoords = deliveryMarkers.map(deliveryMarker => ({
        lat: deliveryMarker.marker.getLatLng().lat,
        lng: deliveryMarker.marker.getLatLng().lng,
        id: deliveryMarker.id
    }));
    
    // Calculer la distance optimale (algorithme du plus proche voisin)
    let currentLat = hubLat;
    let currentLng = hubLng;
    const visited = [];
    
    // Trouver le pickup le plus proche du hub
    let closestIndex = 0;
    let minDistance = Infinity;
    
    pickupCoords.forEach((pickup, index) => {
        const distance = calculateRealDistance(currentLat, currentLng, pickup.lat, pickup.lng);
        if (distance < minDistance) {
            minDistance = distance;
            closestIndex = index;
        }
    });
    
    // Visiter tous les pickups
    visited.push(closestIndex);
    currentLat = pickupCoords[closestIndex].lat;
    currentLng = pickupCoords[closestIndex].lng;
    
    while (visited.length < pickupCoords.length) {
        let nextIndex = -1;
        let minDistance = Infinity;
        
        pickupCoords.forEach((pickup, index) => {
            if (!visited.includes(index)) {
                const distance = calculateRealDistance(currentLat, currentLng, pickup.lat, pickup.lng);
                if (distance < minDistance) {
                    minDistance = distance;
                    nextIndex = index;
                }
            }
        });
        
        if (nextIndex !== -1) {
            totalDistance += minDistance;
            visited.push(nextIndex);
            currentLat = pickupCoords[nextIndex].lat;
            currentLng = pickupCoords[nextIndex].lng;
        }
    }
    
    // Distance du dernier pickup vers le hub
    const distanceToHub = calculateRealDistance(currentLat, currentLng, hubLat, hubLng);
    totalDistance += distanceToHub;
    
    // Calcul du temps basé sur la distance réelle
    const collectionTime = activeDeliveries * 5; // 5 min par collecte
    const travelTime = (totalDistance / 25) * 60; // 25 km/h en ville
    const totalTime = Math.round(collectionTime + travelTime);
    
    // Mettre à jour les statistiques
    document.getElementById('total-distance').textContent = `${totalDistance.toFixed(1)} km`;
    document.getElementById('remaining-time').textContent = `${totalTime} min`;
    
    // Afficher un message de confirmation
     alert(`Route optimized!\nTotal distance: ${totalDistance.toFixed(1)} km\nEstimated time: ${totalTime} minutes`);
}


// Naviguer vers le hub
function navigateToHub() {
    const hubLat = hubLocation.lat;
    const hubLng = hubLocation.lng;
    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${hubLat},${hubLng}`;
    window.open(googleMapsUrl, '_blank');
}


// Naviguer vers une destination
function navigateTo(lat, lng) {
    // Ouvrir Google Maps pour la navigation
    const googleMapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    window.open(googleMapsUrl, '_blank');
}

// Initialiser la carte quand la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

// Mise à jour périodique de la position (toutes les 30 secondes)
setInterval(() => {
    if (navigator.geolocation && courierMarker) {
        navigator.geolocation.getCurrentPosition((position) => {
            const newLat = position.coords.latitude;
            const newLng = position.coords.longitude;
            
            courierMarker.setLatLng([newLat, newLng]);
        });
    }
}, 30000);
</script>

@endsection
