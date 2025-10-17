<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PickupController extends Controller
{
    // GET /pickups/create?waste_item_id=XX
    public function create(Request $request)
    {
        $wasteItemId = $request->query('waste_item_id');
        $wasteItem = $wasteItemId ? WasteItem::findOrFail($wasteItemId) : null;

        return view('pickups.create', compact('wasteItem'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = \App\Models\Pickup::with(['wasteItem:id,title,generator_id'])
            ->latest();

        if ($user && ($user->role->value ?? $user->role) === 'courier') {
            // Courrier : voit les pickups libres + les siens
            $query->where(function ($q) use ($user) {
                $q->whereNull('courier_id')
                    ->orWhere('courier_id', $user->id);
            });
        } else {
            // Autres rôles : ta logique existante (ex: générateur voit ses propres listings)
            $query->whereHas('wasteItem', fn ($q) => $q->where('generator_id', $user->id));
        }

        $pickups = $query->paginate(15)->withQueryString();

        return view('pickups.index', compact('pickups'));
    }

    public function store(Request $request)
    {
        // ⚠️ Les noms doivent correspondre aux name= de ton formulaire
        $data = $request->validate([
            'waste_item_id' => ['required', 'integer', 'exists:waste_items,id'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'scheduled_pickup_window_start' => ['nullable', 'date'],
            'scheduled_pickup_window_end' => ['nullable', 'date', 'after_or_equal:scheduled_pickup_window_start'],
            'status' => ['required', 'in:scheduled,assigned,in_transit,picked,failed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['tracking_code'] = Str::upper(Str::random(12));

        // création
        Pickup::create([
            'waste_item_id' => $data['waste_item_id'],
            'pickup_address' => $data['pickup_address'],
            'scheduled_pickup_window_start' => $data['scheduled_pickup_window_start'] ?? null,
            'scheduled_pickup_window_end' => $data['scheduled_pickup_window_end'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'tracking_code' => $data['tracking_code'],
        ]);

        // ➜ redirige vers la liste avec un flash
        return redirect()->route('pickups.index')
            ->with('ok', 'Pickup saved successfully.');
    }
    // POST /pickups
    /* public function store(Request $request)
     {
         $data = $request->validate([
             'waste_item_id'                   => ['required', 'integer', 'exists:waste_items,id'],
             'pickup_address'                  => ['required', 'string', 'max:255'],
             'scheduled_pickup_window_start'   => ['nullable', 'date'],
             'scheduled_pickup_window_end'     => ['nullable', 'date', 'after_or_equal:scheduled_pickup_window_start'],
             'status'                          => ['required', 'in:scheduled,assigned,in_transit,picked,failed,cancelled'],
             'notes'                           => ['nullable', 'string'],
         ]);

         // auto: code de suivi et coursier = l'utilisateur authentifié (si logique souhaitée)
         $data['tracking_code'] = Str::upper(Str::random(12));
         $data['courier_id']    = auth()->id(); // si tu veux affecter le créateur comme "courier"

         $pickup = Pickup::create($data);

         return redirect()
             ->route('pickups.show', $pickup)
             ->with('ok', 'Pickup created.');
     }*/

    public function show(Pickup $pickup)
    {
        return view('pickups.show', compact('pickup'));
    }

    public function edit(\App\Models\Pickup $pickup)
    {
        // $this->authorize('update', $pickup); // si tu as une policy
        $statuses = ['scheduled', 'assigned', 'in_transit', 'picked', 'failed', 'cancelled'];

        return view('pickups.edit', [
            'pickup' => $pickup->load('wasteItem:id,title'),
            'statuses' => $statuses,
        ]);
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Pickup $pickup)
    {
        // $this->authorize('update', $pickup);

        $data = $request->validate([
            'pickup_address' => ['required', 'string', 'max:255'],
            'scheduled_pickup_window_start' => ['nullable', 'date'],
            'scheduled_pickup_window_end' => ['nullable', 'date', 'after_or_equal:scheduled_pickup_window_start'],
            'status' => ['required', 'in:scheduled,assigned,in_transit,picked,failed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $pickup->fill($data)->save();

        return redirect()->route('pickups.index')->with('success', 'Pickup updated.');
    }

    public function destroy(Request $request, \App\Models\Pickup $pickup)
    {
        // $this->authorize('delete', $pickup);
        $pickup->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->noContent();
        }

        return redirect()->route('pickups.index')->with('success', 'Pickup deleted.');
    }
}
