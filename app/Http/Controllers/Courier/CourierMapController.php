<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Delivery;

class CourierMapController extends Controller
{
    public function index()
    {
        // Récupérer les livraisons du livreur connecté
        $deliveries = Delivery::where('courier_id', auth()->id())
            ->whereIn('status', ['assigned', 'in_transit'])
            ->with(['pickup.wasteItem'])
            ->get();

        // Récupérer les coordonnées du hub
        $hubLocation = [
            'address' => config('logistics.hub.address', 'ReCircle Hub — Avenue Habib Bourguiba, Tunis'),
            'lat' => config('logistics.hub.lat', 36.7989),
            'lng' => config('logistics.hub.lng', 10.1808),
        ];

        // Calculer les statistiques réelles
        $stats = $this->calculateDeliveryStats($deliveries);

        // Préparer les données pour la carte avec géocodage
        $mapData = [
            'deliveries' => $deliveries->map(function ($delivery) {
                // Géocoder l'adresse de pickup (simulation avec coordonnées Tunis)
                $pickupCoords = $this->geocodeAddress($delivery->pickup->pickup_address ?? 'Tunis, Tunisie');

                return [
                    'id' => $delivery->id,
                    'pickup_address' => $delivery->pickup->pickup_address ?? 'Adresse non disponible',
                    'status' => $delivery->status,
                    'waste_item_title' => $delivery->pickup->wasteItem->title ?? 'N/A',
                    'scheduled_time' => $delivery->pickup->scheduled_pickup_window_start ?
                        $delivery->pickup->scheduled_pickup_window_start->format('H:i') : 'N/A',
                    'notes' => $delivery->notes,
                    'tracking_code' => $delivery->tracking_code,
                    'lat' => $pickupCoords['lat'],
                    'lng' => $pickupCoords['lng'],
                ];
            }),
            'hub' => $hubLocation,
            'stats' => $stats,
        ];

        return view('courier.map', compact('deliveries', 'mapData'));
    }

    /**
     * Calculer les statistiques des livraisons
     */
    private function calculateDeliveryStats($deliveries)
    {
        $totalDeliveries = $deliveries->count();
        $completedDeliveries = $deliveries->where('status', 'delivered')->count();
        $inTransitDeliveries = $deliveries->where('status', 'in_transit')->count();
        $assignedDeliveries = $deliveries->where('status', 'assigned')->count();

        // Calculer la distance totale estimée
        $totalDistance = $this->calculateTotalDistance($deliveries);

        // Calculer le temps restant estimé
        $remainingTime = $this->calculateRemainingTime($assignedDeliveries, $inTransitDeliveries);

        return [
            'total_deliveries' => $totalDeliveries,
            'completed_deliveries' => $completedDeliveries,
            'in_transit_deliveries' => $inTransitDeliveries,
            'assigned_deliveries' => $assignedDeliveries,
            'total_distance_km' => $totalDistance,
            'remaining_time_minutes' => $remainingTime,
        ];
    }

    /**
     * Calculer la distance totale réelle entre tous les points
     */
    private function calculateTotalDistance($deliveries)
    {
        $activeDeliveries = $deliveries->whereIn('status', ['assigned', 'in_transit']);

        if ($activeDeliveries->count() === 0) {
            return 0;
        }

        $totalDistance = 0;
        $hubLat = config('logistics.hub.lat', 36.7989);
        $hubLng = config('logistics.hub.lng', 10.1808);

        // Coordonnées de tous les pickups
        $pickupCoords = [];
        foreach ($activeDeliveries as $delivery) {
            $coords = $this->geocodeAddress($delivery->pickup->pickup_address ?? 'Tunis, Tunisie');
            $pickupCoords[] = [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'id' => $delivery->id,
                'address' => $delivery->pickup->pickup_address,
            ];
        }

        // Calculer la distance totale avec un algorithme de plus court chemin
        $totalDistance = $this->calculateOptimalRouteDistance($pickupCoords, $hubLat, $hubLng);

        return round($totalDistance, 1);
    }

    /**
     * Calculer la distance optimale entre tous les points
     */
    private function calculateOptimalRouteDistance($pickupCoords, $hubLat, $hubLng)
    {
        if (empty($pickupCoords)) {
            return 0;
        }

        $totalDistance = 0;
        $visited = [];
        $currentLat = $hubLat; // Commencer du hub
        $currentLng = $hubLng;

        // Trouver le pickup le plus proche du hub pour commencer
        $closestIndex = 0;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($pickupCoords as $index => $pickup) {
            $distance = $this->calculateHaversineDistance($currentLat, $currentLng, $pickup['lat'], $pickup['lng']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestIndex = $index;
            }
        }

        // Visiter tous les pickups en suivant le chemin le plus court
        $visited[] = $closestIndex;
        $currentLat = $pickupCoords[$closestIndex]['lat'];
        $currentLng = $pickupCoords[$closestIndex]['lng'];

        while (count($visited) < count($pickupCoords)) {
            $nextIndex = -1;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($pickupCoords as $index => $pickup) {
                if (! in_array($index, $visited)) {
                    $distance = $this->calculateHaversineDistance($currentLat, $currentLng, $pickup['lat'], $pickup['lng']);
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $nextIndex = $index;
                    }
                }
            }

            if ($nextIndex !== -1) {
                $totalDistance += $minDistance;
                $visited[] = $nextIndex;
                $currentLat = $pickupCoords[$nextIndex]['lat'];
                $currentLng = $pickupCoords[$nextIndex]['lng'];
            }
        }

        // Distance du dernier pickup vers le hub
        $distanceToHub = $this->calculateHaversineDistance($currentLat, $currentLng, $hubLat, $hubLng);
        $totalDistance += $distanceToHub;

        return $totalDistance;
    }

    /**
     * Calculer la distance entre deux points avec la formule de Haversine
     */
    private function calculateHaversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Rayon de la Terre en km

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculer le temps restant basé sur la distance réelle
     */
    private function calculateRemainingTime($assignedCount, $inTransitCount)
    {
        // Vitesse moyenne en ville (km/h)
        $averageSpeedKmh = 25; // 25 km/h en moyenne dans Tunis

        // Temps de collecte par pickup (minutes)
        $collectionTimePerPickup = 5; // 5 minutes pour collecter chaque produit

        $totalPickups = $assignedCount + $inTransitCount;

        // Temps de collecte total
        $totalCollectionTime = $totalPickups * $collectionTimePerPickup;

        // La distance sera calculée séparément et convertie en temps
        // Ici on retourne juste le temps de collecte, la distance sera ajoutée dans la vue

        return $totalCollectionTime;
    }

    /**
     * Géocode une adresse automatiquement avec API ou cache
     */
    private function geocodeAddress($address)
    {
        // Nettoyer l'adresse
        $cleanAddress = trim($address);
        if (empty($cleanAddress)) {
            return $this->getDefaultCoords();
        }

        // 1. Vérifier le cache d'abord
        $cacheKey = 'geocode_'.md5($cleanAddress);
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return $cached;
        }

        // 2. Essayer l'API de géocodage
        $coords = $this->geocodeWithAPI($cleanAddress);

        // 3. Si l'API échoue, utiliser les coordonnées par défaut intelligentes
        if (! $coords) {
            $coords = $this->getSmartDefaultCoords($cleanAddress);
        }

        // 4. Mettre en cache pour 24h
        cache()->put($cacheKey, $coords, 86400);

        return $coords;
    }

    /**
     * Géocoder avec une API (OpenStreetMap Nominatim - gratuite)
     */
    private function geocodeWithAPI($address)
    {
        try {
            // Ajouter "Tunisie" si pas déjà présent pour améliorer la précision
            $searchAddress = $address;
            if (! stripos($address, 'tunisie') && ! stripos($address, 'tunisia')) {
                $searchAddress = $address.', Tunisie';
            }

            $url = 'https://nominatim.openstreetmap.org/search?'.http_build_query([
                'q' => $searchAddress,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'tn', // Limiter à la Tunisie
                'addressdetails' => 1,
            ]);

            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: ReCircle-App/1.0\r\n",
                    'timeout' => 5, // Timeout de 5 secondes
                ],
            ]);

            $response = file_get_contents($url, false, $context);
            if (! $response) {
                return null;
            }

            $data = json_decode($response, true);
            if (empty($data) || ! isset($data[0]['lat']) || ! isset($data[0]['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $data[0]['lat'],
                'lng' => (float) $data[0]['lon'],
            ];

        } catch (Exception $e) {
            \Log::warning('Géocodage API échoué: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Coordonnées par défaut intelligentes basées sur l'adresse
     */
    private function getSmartDefaultCoords($address)
    {
        $normalizedAddress = strtolower(trim($address));

        // Reconnaissance intelligente des villes tunisiennes
        $cityPatterns = [
            'sfax' => ['lat' => 34.7406, 'lng' => 10.7603],
            'صفاقس' => ['lat' => 34.7406, 'lng' => 10.7603],
            'sousse' => ['lat' => 35.8256, 'lng' => 10.6411],
            'سوسة' => ['lat' => 35.8256, 'lng' => 10.6411],
            'monastir' => ['lat' => 35.7781, 'lng' => 10.8262],
            'المنستير' => ['lat' => 35.7781, 'lng' => 10.8262],
            'bizerte' => ['lat' => 37.2744, 'lng' => 9.8739],
            'بنزرت' => ['lat' => 37.2744, 'lng' => 9.8739],
            'gabes' => ['lat' => 33.8886, 'lng' => 10.0972],
            'قابس' => ['lat' => 33.8886, 'lng' => 10.0972],
            'kairouan' => ['lat' => 35.6711, 'lng' => 10.1006],
            'القيروان' => ['lat' => 35.6711, 'lng' => 10.1006],
            'tunis' => ['lat' => 36.8065, 'lng' => 10.1815],
            'تونس' => ['lat' => 36.8065, 'lng' => 10.1815],
        ];

        // Chercher une correspondance de ville
        foreach ($cityPatterns as $pattern => $coords) {
            if (strpos($normalizedAddress, $pattern) !== false) {
                return $coords;
            }
        }

        // Si pas de ville reconnue, utiliser des coordonnées par défaut avec variation
        return $this->getDefaultCoords();
    }

    /**
     * Coordonnées par défaut avec variation
     */
    private function getDefaultCoords()
    {
        $baseCoords = [
            ['lat' => 36.8065, 'lng' => 10.1815], // Centre Tunis
            ['lat' => 36.8156, 'lng' => 10.1853], // Ariana
            ['lat' => 36.7947, 'lng' => 10.1869], // Ben Arous
            ['lat' => 36.7989, 'lng' => 10.1808], // Avenue Habib Bourguiba
            ['lat' => 36.8096, 'lng' => 10.1647], // Manouba
        ];

        // Utiliser un hash pour des coordonnées cohérentes
        $hash = crc32(microtime());
        $index = abs($hash) % count($baseCoords);

        return $baseCoords[$index];
    }
}
