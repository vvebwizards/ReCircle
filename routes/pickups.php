<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;   // << important
use App\Models\Pickup;
use App\Http\Controllers\PickupController;

Route::middleware(['jwt.auth'])->group(function () {

    // Formulaire de création
    Route::get('/pickups/create', function () {
        return view('pickups.create');
    })->name('pickups.create');

    //affichage du formulaire   
    Route::get('/pickups', function () {
        $pickups = Pickup::orderByDesc('created_at')->paginate(10);
        return view('pickups.index', compact('pickups'));
    })->name('pickups.index');

    //actions
     Route::resource('pickups', PickupController::class)
         ->only(['index','edit','update','destroy']);

    // Enregistrement
    Route::post('/pickups', function (Request $request) {

        $data = $request->validate([
            'pickup_address' => ['required','string','max:255'],
            'scheduled_pickup_window_start' => ['nullable','date'],
            'scheduled_pickup_window_end'   => ['nullable','date','after_or_equal:scheduled_pickup_window_start'],
            'status' => ['required','in:scheduled,assigned,in_transit,picked,failed,cancelled'],
            'notes'  => ['nullable','string'],
        ]);
          $userId = $request->user()->id;   // ou Auth::id();

        // 🚫 On n'utilise PAS match_id ni courier_id côté formulaire.
        // ✅ On impose les valeurs côté serveur :
        $data['match_id']      = null; // jamais 0
        $data['courier_id']    = null;
        $data['tracking_code'] = strtoupper(bin2hex(random_bytes(6)));
        $data['created_at']    = now();
        $data['updated_at']    = now();
        $data['courier_id']    = $userId;

        DB::table('pickups')->insert($data);

        return redirect()->route('pickups.create')->with('ok', 'Pickup créé.');
    })->name('pickups.store');
});
