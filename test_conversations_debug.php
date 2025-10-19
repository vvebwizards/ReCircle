<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Debug des conversations de chat\n\n";

// Récupérer l'utilisateur actuel (simuler l'utilisateur connecté)
$user = App\Models\User::where('role', 'generator')->first();

if (! $user) {
    echo "❌ Utilisateur non trouvé\n";
    exit;
}

echo "👤 Utilisateur connecté: {$user->name} (ID: {$user->id})\n\n";

// Récupérer tous les messages
$allMessages = App\Models\ChatMessage::with(['sender', 'receiver', 'pickup'])
    ->where(function ($query) use ($user) {
        $query->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id);
    })
    ->orderBy('created_at', 'desc')
    ->get();

echo '📊 Messages trouvés: '.$allMessages->count()."\n\n";

// Grouper par utilisateur ET pickup
$groupedMessages = $allMessages->groupBy(function ($message) use ($user) {
    $otherUserId = $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;
    $pickupId = $message->pickup_id ?? 'general';

    return $otherUserId.'_'.$pickupId;
});

echo '🔍 Groupes de conversations trouvés: '.$groupedMessages->count()."\n\n";

foreach ($groupedMessages as $groupKey => $messages) {
    $latestMessage = $messages->sortByDesc('created_at')->first();
    $otherUser = $latestMessage->sender_id === $user->id
        ? $latestMessage->receiver
        : $latestMessage->sender;

    echo "📝 Groupe: {$groupKey}\n";
    echo "   👤 Avec: {$otherUser->name} (ID: {$otherUser->id})\n";
    echo '   📦 Pickup: '.($latestMessage->pickup ? "#{$latestMessage->pickup->id}" : 'Général')."\n";
    echo '   💬 Messages: '.$messages->count()."\n";
    echo '   📅 Dernier: '.$latestMessage->created_at->format('d/m/Y H:i')."\n";
    echo '   💬 Dernier message: "'.Str::limit($latestMessage->message, 50)."\"\n";
    echo "\n";
}

echo "🎯 Résultat attendu:\n";
echo '   📝 Vous devriez voir '.$groupedMessages->count()." conversations séparées\n";
echo "   📦 Chaque pickup aura sa propre conversation\n";
echo "   💬 Les conversations générales (sans pickup) seront séparées\n";

echo "\n🌐 Testez maintenant: http://127.0.0.1:8000/chat\n";
echo "✨ Vous devriez voir plusieurs conversations distinctes !\n";
