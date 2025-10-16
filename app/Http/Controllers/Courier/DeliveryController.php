<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Pickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DeliveryController extends Controller
{
    /* ------------ helpers ------------ */
    private function ensureCourier()
    {
        $user = auth()->user();
        abort_if(!$user || $user->role->value !== 'courier', 403);
        return $user;
    }

    /* ------------ list ------------ */
    public function index(Request $request)
    {
        $user = $this->ensureCourier();
        $q = trim($request->get('q',''));

        $deliveries = Delivery::with([
                'pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end',
                'pickup.wasteItem:id,title'
            ])
            ->forCourier($user->id)   // scope sur le modèle Delivery
            ->active()               // status actifs & non soft-deleted
            ->when($q !== '', function ($b) use ($q) {
                $b->where('tracking_code', 'like', "%$q%")
                  ->orWhereHas('pickup.wasteItem', fn($w) => $w->where('title','like',"%$q%"))
                  ->orWhereHas('pickup', fn($p) => $p->where('pickup_address','like',"%$q%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('courier.deliveries.index', compact('deliveries'));
    }
    public function completed(Request $request)
{
    $user = $this->ensureCourier();

    $deliveries = Delivery::with(['pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end','pickup.wasteItem:id,title'])
        ->forCourier($user->id)
        ->completed()            // scope: delivered / failed / cancelled
        ->latest('arrived_hub_at')
        ->paginate(15)
        ->withQueryString();

    return view('courier.deliveries.index', [
        'deliveries' => $deliveries,
        'tab' => 'completed',
    ]);
}

    /* ------------ select/create from pickup ------------ */
    public function createFromPickup(Pickup $pickup)
    {
        $user = $this->ensureCourier();

        // si une delivery ACTIVE existe déjà pour ce pickup → retour
        $hasActive = Delivery::where('pickup_id', $pickup->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasActive) {
            return redirect()->route('deliveries.index')
                ->with('ok', "Pickup #{$pickup->id} a déjà une delivery active.");
        }

        $defaults = [
            'hub_address'   => config('delivery.hub.address', 'ReCircle Hub — 12 Rue Exemple, Tunis'),
            'hub_lat'       => config('delivery.hub.lat', 36.8065),
            'hub_lng'       => config('delivery.hub.lng', 10.1815),
            'courier_phone' => $user->phone ?? '',
        ];

        return view('courier.deliveries.create', [
            'pickup'   => $pickup->load('wasteItem:id,title'),
            'defaults' => $defaults,
        ]);
    }

    public function storeFromPickup(Request $request, Pickup $pickup)
    {
        $user = $this->ensureCourier();

        $data = $request->validate([
            'courier_phone' => ['required','string','max:40'],
            'hub_address'   => ['required','string','max:255'],
            'hub_lat'       => ['nullable','numeric'],
            'hub_lng'       => ['nullable','numeric'],
            'notes'         => ['nullable','string'],
            'start_now'     => ['nullable','boolean'],
        ]);

        abort_if(
            Delivery::where('pickup_id', $pickup->id)->whereNull('deleted_at')->exists(),
            422, 'Delivery active déjà existante pour ce pickup.'
        );

        DB::transaction(function () use ($data, $pickup, $user, $request) {
            $delivery = Delivery::create([
                'pickup_id'     => $pickup->id,
                'courier_id'    => $user->id,
                'courier_phone' => $data['courier_phone'],
                'hub_address'   => $data['hub_address'],
                'hub_lat'       => $data['hub_lat'] ?? null,
                'hub_lng'       => $data['hub_lng'] ?? null,
                'status'        => 'assigned',
                'tracking_code' => $pickup->tracking_code ?: Str::upper(Str::random(12)),
                'assigned_at'   => now(),
                'notes'         => $data['notes'] ?? null,
            ]);

            // option: démarrer immédiatement
            if ($request->boolean('start_now')) {
                $delivery->update([
                    'status'        => 'in_transit',
                    'picked_up_at'  => now(),
                ]);
            }

            // garder la pickup synchronisée (facultatif)
            $pickup->update([
                'courier_id' => $user->id,
                'status'     => $delivery->status === 'in_transit' ? 'in_transit' : 'assigned',
            ]);
        });

        return redirect()->route('deliveries.index')
            ->with('ok', "Delivery pour pickup #{$pickup->id} créée.");
    }

    /* ------------ edit/update ------------ */
    public function edit(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        abort_if($delivery->courier_id && $delivery->courier_id !== $user->id, 403);

        $allowed = match ($delivery->status) {
            'scheduled', 'assigned' => ['scheduled','assigned','in_transit','cancelled'],
            'in_transit'            => ['in_transit','delivered','failed'],
            default                 => [], // delivered/failed/cancelled
        };

        return view('courier.deliveries.edit', compact('delivery','allowed'));
    }

   public function update(Request $request, \App\Models\Delivery $delivery)
{
    $user = $this->ensureCourier();
    abort_if($delivery->courier_id && $delivery->courier_id !== $user->id, 403);

    $allowed = match ($delivery->status) {
        'scheduled', 'assigned' => ['scheduled','assigned','in_transit','cancelled'],
        'in_transit'            => ['in_transit','delivered','failed'],
        default                 => [],
    };

    $data = $request->validate([
        'status'        => ['required', Rule::in($allowed)],
        'courier_phone' => ['nullable','string','max:40'],
        'notes'         => ['nullable','string'],
    ]);

    // ✅ Cas "Cancelled" -> on supprime la delivery et on libère le pickup
    if ($data['status'] === 'cancelled') {
        // libérer le pickup pour qu'il réapparaisse dans la liste
        if ($delivery->pickup) {
            $delivery->pickup->update([
                'courier_id' => null,
                'status'     => 'scheduled',   // ou laisse comme tu veux
            ]);
        }

        // suppression définitive de la ligne (pas soft)
        $delivery->forceDelete(); // si tu préfères soft: $delivery->delete();

        return redirect()
            ->route('deliveries.index')
            ->with('ok', "Delivery #{$delivery->id} cancelled and removed.");
    }

    // --- autres statuts (logique inchangée) ---
    if (is_null($delivery->courier_id)) {
        $delivery->courier_id = $user->id;
    }

    $delivery->courier_phone = $data['courier_phone'] ?? $delivery->courier_phone;
    $delivery->notes         = $data['notes'] ?? $delivery->notes;
    $delivery->status        = $data['status'];

    if ($delivery->status === 'in_transit' && is_null($delivery->picked_up_at)) {
        $delivery->picked_up_at = now();
    }
    if ($delivery->status === 'delivered' && is_null($delivery->arrived_hub_at)) {
        $delivery->arrived_hub_at = now();
    }

    $delivery->save();

    return redirect()->route('deliveries.index')->with('ok', "Delivery #{$delivery->id} updated.");
}
public function availablePickups(Request $request)
{
    $this->ensureCourier();
    $q = trim($request->get('q', ''));

    $pickups = \App\Models\Pickup::with(['wasteItem:id,title'])
        ->whereNull('courier_id')                // ← seulement les non-assignés
        ->where('status', 'scheduled')           // (optionnel) filtre statut
        ->when($q !== '', function ($b) use ($q) {
            $b->where('pickup_address','like',"%$q%")
              ->orWhere('tracking_code','like',"%$q%")
              ->orWhereHas('wasteItem', fn($w)=>$w->where('title','like',"%$q%"));
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('courier.deliveries.available', compact('pickups', 'q'));
}
    /* ------------ quick actions ------------ */
    public function markInTransit(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        if (is_null($delivery->courier_id)) {
            $delivery->courier_id = $user->id;
        } elseif ($delivery->courier_id !== $user->id) {
            abort(403, 'This delivery is assigned to another courier.');
        }
        abort_if(!in_array($delivery->status, ['scheduled','assigned']), 422);

        $delivery->update([
            'status'       => 'in_transit',
            'picked_up_at' => $delivery->picked_up_at ?? now(),
        ]);

        optional($delivery->pickup)->update(['status' => 'in_transit']);

        return back()->with('ok', "Delivery #{$delivery->id} est maintenant In transit.");
    }

    public function markDelivered(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        abort_if($delivery->courier_id !== $user->id, 403);
        abort_if($delivery->status !== 'in_transit', 422);

        $delivery->update([
            'status'         => 'delivered',
            'arrived_hub_at' => now(),
        ]);

        optional($delivery->pickup)->update(['status' => 'picked']); // si tu veux

        return back()->with('ok', "Delivery #{$delivery->id} livrée au hub.");
    }
}