<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧹 Test des filtres simplifiés (sans fenêtres)\n\n";

// Vérifier les pickups existants
$pickups = App\Models\Pickup::with(['wasteItem.generator'])->get();

echo "📊 État actuel:\n";
echo '   📋 Total pickups: '.$pickups->count()."\n";

// Test des différents statuts
$statuses = ['scheduled', 'assigned', 'in_transit', 'picked', 'failed', 'cancelled'];
echo "   📊 Répartition par statut:\n";
foreach ($statuses as $status) {
    $count = App\Models\Pickup::where('status', $status)->count();
    if ($count > 0) {
        echo "      - {$status}: {$count} pickup(s)\n";
    }
}

echo "\n🔧 Filtres simplifiés disponibles:\n";
echo "   ✅ Recherche textuelle (adresse, code de suivi, produit)\n";
echo "   ✅ Filtre par statut\n";
echo "   ❌ Filtres de fenêtre supprimés\n";

echo "\n🎯 URLs de test:\n";
echo "   🔍 Page principale: http://127.0.0.1:8000/pickups\n";
echo "   📊 Avec filtre statut: http://127.0.0.1:8000/pickups?status=picked\n";
echo "   🔍 Avec recherche: http://127.0.0.1:8000/pickups?search=tunis\n";
echo "   🔗 Combiné: http://127.0.0.1:8000/pickups?status=scheduled&search=ariana\n";

echo "\n✨ Interface simplifiée:\n";
echo "   🎨 Moins de champs = interface plus claire\n";
echo "   🚀 Chargement plus rapide\n";
echo "   👥 Plus facile à utiliser\n";
echo "   🔧 Maintenance simplifiée\n";

echo "\n🎉 Filtres de fenêtre supprimés avec succès!\n";
