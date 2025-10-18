<?php

use App\Http\Controllers\Admin\AdminDeliveryController;
use App\Http\Controllers\Admin\AdminPickupController;
use App\Http\Controllers\BidController;
// si tu l’utilises dans bids.php aussi
use App\Http\Controllers\Courier\DeliveryController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('jwt.auth')->name('dashboard');

// API endpoint for getting current user data
Route::get('/api/user', function () {
    try {
        $user = auth()->user();
        if ($user) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ]);
        }

        return response()->json(['error' => 'Not authenticated'], 401);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error: '.$e->getMessage()], 500);
    }
})->middleware('jwt.auth');

Route::middleware(['jwt.auth'])->get('/dashboard/bids', [\App\Http\Controllers\DashboardBidController::class, 'index'])->name('dashboard.bids');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

Route::get('/maker/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])
    ->middleware(['jwt.auth'])
    ->name('maker.analytics');

Route::middleware(['jwt.auth'])->get('/maker/bids', [\App\Http\Controllers\MakerBidController::class, 'index'])->name('maker.bids');

Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    // Admin > Deliveries
    Route::prefix('deliveries')->name('admin.deliveries.')->group(function () {
        Route::get('/', [AdminDeliveryController::class, 'index'])->name('index');       // Active
        Route::get('/completed', [AdminDeliveryController::class, 'completed'])->name('completed'); // Completed
        Route::get('/{delivery}', [AdminDeliveryController::class, 'show'])->name('show');         // Détail
        // ➕ nouveaux endpoints
        Route::get('/{delivery}/edit', [AdminDeliveryController::class, 'edit'])->name('edit');
        Route::patch('/{delivery}', [AdminDeliveryController::class, 'update'])->name('update');
        Route::delete('/{delivery}', [AdminDeliveryController::class, 'destroy'])->name('destroy');

    });
    // Admin > Pickups
    Route::prefix('pickups')->name('admin.pickups.')->group(function () {
        Route::get('/', [AdminPickupController::class, 'index'])->name('index');
        Route::get('/{pickup}', [AdminPickupController::class, 'show'])->name('show');

        // NEW
        Route::get('/{pickup}/edit', [\App\Http\Controllers\Admin\AdminPickupController::class, 'edit'])->name('edit');
        Route::put('/{pickup}', [\App\Http\Controllers\Admin\AdminPickupController::class, 'update'])->name('update');
        Route::delete('/{pickup}', [\App\Http\Controllers\Admin\AdminPickupController::class, 'destroy'])->name('destroy');

    });
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth'])->name('admin.users');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggleStatus');

    Route::post('/users/{user}/block', [UserManagementController::class, 'blockUser'])->name('admin.users.block');
    Route::post('/users/{user}/unblock', [UserManagementController::class, 'unblockUser'])->name('admin.users.unblock');
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    require __DIR__.'/admin_listings.php';
});

Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');

require __DIR__.'/auth.php';

require __DIR__.'/materials.php';

require __DIR__.'/products.php';

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/pickups', [PickupController::class, 'index'])
        ->name('pickups.index');
    // Afficher le formulaire de création avec un waste_item pré-sélectionné
    Route::get('/pickups/create', [PickupController::class, 'create'])
        ->name('pickups.create');

    // Sauvegarder un pickup
    Route::post('/pickups', [PickupController::class, 'store'])
        ->name('pickups.store');

    // (optionnel) page de détail une fois créé
    Route::get('/pickups/{pickup}', [PickupController::class, 'show'])
        ->name('pickups.show');

    Route::get('/pickups/{pickup}/edit', [PickupController::class, 'edit'])->name('pickups.edit');     // <- EDIT
    Route::put('/pickups/{pickup}', [PickupController::class, 'update'])->name('pickups.update'); // <- UPDATE
    Route::delete('/pickups/{pickup}', [PickupController::class, 'destroy'])->name('pickups.destroy'); // <- DELETE

    // delevery routes
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('/deliveries/claim/{pickup}', [\App\Http\Controllers\Courier\DeliveryController::class, 'claim'])
        ->name('deliveries.claim');

    Route::patch('/deliveries/{delivery}/start', [DeliveryController::class, 'markInTransit'])->name('deliveries.start');
    Route::patch('/deliveries/{delivery}/delivered', [DeliveryController::class, 'markDelivered'])->name('deliveries.delivered');
    // Deliveries (courier)
    Route::get('/deliveries/{delivery}/edit', [\App\Http\Controllers\Courier\DeliveryController::class, 'edit'])->name('deliveries.edit');
    Route::patch('/deliveries/{delivery}', [\App\Http\Controllers\Courier\DeliveryController::class, 'update'])->name('deliveries.update');

    Route::get('/deliveries/completed', [DeliveryController::class, 'completed'])
        ->name('deliveries.completed');
    // Deliveries (création à partir d’un pickup)
    Route::get('/pickups/{pickup}/select-delivery', [DeliveryController::class, 'createFromPickup'])->name('deliveries.createFromPickup');
    Route::post('/pickups/{pickup}/select-delivery', [DeliveryController::class, 'storeFromPickup'])->name('deliveries.storeFromPickup');
    // Liste des pickups disponibles (courier_id NULL)
    Route::get('/deliveries/pickups', [DeliveryController::class, 'availablePickups'])
        ->name('deliveries.pickups');

    // Créer une delivery à partir d'un pickup disponible
    Route::post('/deliveries/from-pickup/{pickup}', [DeliveryController::class, 'storeFromPickup'])
        ->name('deliveries.fromPickup.store');

    // Carte des livreurs
    Route::get('/map', [\App\Http\Controllers\Courier\CourierMapController::class, 'index'])
        ->name('courier.map');
    /* Route::prefix('deliveries')->name('deliveries.')->middleware(['jwt.auth'])->group(function () {
        // Liste des courses pour le courier
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        // Actions d'état
        Route::patch('/{delivery}/start',    [DeliveryController::class, 'markInTransit'])->name('start');
        Route::patch('/{delivery}/delivered',[DeliveryController::class, 'markDelivered'])->name('delivered');
    });*/
    /* // Formulaire de pickup pour un match donné
     Route::get('/matches/{match}/pickups/create', [PickupController::class, 'create'])
         ->name('pickups.create');

     // Enregistrement du pickup
     Route::post('/matches/{match}/pickups', [PickupController::class, 'store'])
         ->name('pickups.store');

     // (optionnel) liste des pickups
     Route::get('/pickups', [PickupController::class, 'index'])
         ->name('pickups.index');*/

    require __DIR__.'/waste_items.php';

    // Routes pour téléchargement des pickups
    Route::get('/pickups/download/pdf', [App\Http\Controllers\PickupDownloadController::class, 'downloadPDF'])->name('pickups.download.pdf');
    Route::get('/pickups/download/csv', [App\Http\Controllers\PickupDownloadController::class, 'downloadCSV'])->name('pickups.download.csv');
    Route::get('/pickups/download/excel', [App\Http\Controllers\PickupDownloadController::class, 'downloadExcel'])->name('pickups.download.excel');

    // Routes pour le chat
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/mark-read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
});

Route::middleware(['jwt.auth'])->patch(
    '/bids/{bid}/status',
    [BidController::class, 'updateStatus']
)->name('bids.updateStatus');

// Quand le générateur accepte un bid -> on redirige vers le formulaire pickup
Route::middleware(['jwt.auth'])->post(
    '/bids/{bid}/accept',
    [BidController::class, 'updateStatus']
)->name('bids.accept');

require __DIR__.'/marketplace.php';

require __DIR__.'/bids.php';

// routes/web.php
require __DIR__.'/forum.php';

require __DIR__.'/badges.php';

// Routes pour les notifications
Route::middleware('jwt.auth')->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'getUnreadNotifications'])->name('unread');
        Route::get('/all', [App\Http\Controllers\NotificationController::class, 'getAllNotifications'])->name('all');
        Route::get('/count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('count');
        Route::post('/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark.read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark.all.read');
        Route::delete('/delete', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    });
});

// Route de test pour vérifier l'authentification
Route::get('/test-auth', function () {
    $user = auth()->user();
    if ($user) {
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ]);
    } else {
        return response()->json(['authenticated' => false], 401);
    }
});
