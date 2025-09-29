<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PickupController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/maker/dashboard', function () {
    return view('maker.dashboard');
})->name('maker.dashboard');

//pickup
Route::view('/pickups/create', 'pickups.create')->name('pickups.create');
/* --- ENREGISTREMENT --- */
Route::post('/pickups', function (Request $request) {
    $data = $request->validate([
        'pickup_address' => ['required','string','max:255'],
        'scheduled_pickup_window_start' => ['nullable','date'],
        'scheduled_pickup_window_end'   => ['nullable','date','after_or_equal:scheduled_pickup_window_start'],
        'notes' => ['nullable','string'],
        'status' => ['required','in:scheduled,assigned,in_transit,picked,failed,cancelled'],
    ]);

        $userId = $request->user()->id; // fonctionne avec ton middleware jwt.auth

    // Valeurs FIXES côté serveur (jamais dans le formulaire)
    $data['match_id']      = 0;            // <--- demandé
    $data['courier_id']    = null;         // pas encore assigné
    $data['tracking_code'] = Str::upper(Str::random(12));
    $data['created_at']    = now();
    $data['updated_at']    = now();

    DB::table('pickups')->insert($data);

    return back()->with('ok', 'Pickup enregistré.');
})->name('pickups.store');
///////////
/*Route::resource('pickups', PickupController::class)
     ->only(['index','create','store','edit','update','destroy','show']);
     
// action rapide pour que le livreur “claim” un pickup
Route::post('/pickups/{pickup}/claim', [PickupController::class, 'claim'])
     ->name('pickups.claim');
     
// marquer comme “picked up”
Route::post('/pickups/{pickup}/mark-picked', [PickupController::class, 'markPicked'])
     ->name('pickups.markPicked');*/
////////////
// Admin routes (role protection removed per request)
Route::prefix('admin')->middleware(['jwt.auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->middleware(['jwt.auth'])->name('admin.users');
    Route::post('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
    // Admin listings routes (limited CRUD: no create/store)
    require __DIR__.'/admin_listings.php';
});
// Security settings (2FA) page — gated client-side via /api/auth/me
Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');

// Auth routes
require __DIR__.'/auth.php';

// Pickup routes
require __DIR__.'/pickups.php';

// Material routes
require __DIR__.'/materials.php';
// Waste item routes (generator-facing)
// Generator waste item routes (role protection removed per request)
Route::middleware(['jwt.auth'])->group(function () {
    require __DIR__.'/waste_items.php';
});
