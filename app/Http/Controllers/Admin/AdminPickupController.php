<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pickup;
use Illuminate\Http\Request;

class AdminPickupController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $pickups = Pickup::with(['wasteItem:id,title'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($b) use ($q) {
                    $b->where('pickup_address', 'like', "%$q%")
                        ->orWhere('tracking_code', 'like', "%$q%")
                        ->orWhere('status', 'like', "%$q%")
                        ->orWhereHas('wasteItem', fn ($w) => $w->where('title', 'like', "%$q%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pickups.index', compact('pickups'));
    }

    public function show(Pickup $pickup)
    {
        $pickup->load(['wasteItem:id,title']);

        return view('admin.pickups.show', compact('pickup'));
    }

    public function edit(Pickup $pickup)
    {
        $pickup->load('wasteItem:id,title');

        return view('admin.pickups.edit', compact('pickup'));
    }

    public function update(Request $request, Pickup $pickup)
    {
        $data = $request->validate([
            'pickup_address' => ['required', 'string', 'max:255'],
            'scheduled_pickup_window_start' => ['nullable', 'date'],
            'scheduled_pickup_window_end' => ['nullable', 'date', 'after_or_equal:scheduled_pickup_window_start'],
            'status' => ['required', 'in:scheduled,assigned,in_transit,picked,failed,cancelled'],
            'tracking_code' => ['nullable', 'string', 'max:40'],
            'courier_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $pickup->update($data);

        // -> on reste dans l'admin après maj
        return redirect()
            ->route('admin.pickups.index', request()->query()) // garde la page & la recherche si présents
            ->with('ok', "Pickup #{$pickup->id} updated.");
    }

    public function destroy(Request $request, Pickup $pickup)
    {
        $pickup->delete();

        // Si suppression via fetch (AJAX) depuis l’index : renvoie 204 pour rester sur place
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->noContent();
        }

        // Sinon, on revient à l’index admin avec un flash
        return redirect()
            ->route('admin.pickups.index', request()->query())
            ->with('ok', "Pickup #{$pickup->id} deleted.");
    }
}
