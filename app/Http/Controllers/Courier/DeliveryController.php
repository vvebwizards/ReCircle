<?php
namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Pickup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    private function ensureCourier()
    {
        $user = auth()->user();
        abort_if(!$user || $user->role->value !== 'courier', 403);
        return $user;
    }

    // --- liste (tu l’as déjà) ---

    // --- NEW: formulaire de “Select delivery” à partir d’un pickup ---
    public function createFromPickup(Pickup $pickup)
    {
        $user = $this->ensureCourier();

        // Empêcher doublon: une delivery par pickup
        $existing = Delivery::where('pickup_id', $pickup->id)->first();
        if ($existing) {
            return redirect()
                ->route('deliveries.index')
                ->with('ok', "A delivery already exists for pickup #{$pickup->id}.");
        }

        // valeurs par défaut Hub (Cas A — Hub fixe)
        $defaults = [
            'hub_address' => config('delivery.hub.address', 'ReCircle Hub — 12 Rue Exemple, Tunis'),
            'hub_lat'     => config('delivery.hub.lat', 36.8065),
            'hub_lng'     => config('delivery.hub.lng', 10.1815),
            'courier_phone' => $user->phone ?? '',
        ];

        return view('courier.deliveries.create', [
            'pickup'   => $pickup->load('wasteItem:id,title'),
            'defaults' => $defaults,
        ]);
    }

    // --- NEW: enregistrement de la delivery ---
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

        // une seule delivery par pickup
        $delivery = Delivery::firstOrCreate(
            ['pickup_id' => $pickup->id],
            [
                'courier_id'   => $user->id,
                'courier_phone'=> $data['courier_phone'],
                'hub_address'  => $data['hub_address'],
                'hub_lat'      => $data['hub_lat'] ?? null,
                'hub_lng'      => $data['hub_lng'] ?? null,
                'status'       => 'assigned',                 // assignée au livreur
                'tracking_code'=> $pickup->tracking_code ?: Str::upper(Str::random(12)),
                'assigned_at'  => now(),
                'notes'        => $data['notes'] ?? null,
            ]
        );

        // Si “démarrer maintenant”, passer en transit + timestamp de prise
        if ($request->boolean('start_now')) {
            $delivery->status       = 'in_transit';
            $delivery->picked_up_at = now();
            $delivery->save();
        }

        return redirect()
            ->route('deliveries.index')
            ->with('ok', "Delivery for pickup #{$pickup->id} created.");
    }

    // --- EXISTANT (index / markInTransit / markDelivered) ---
    public function index(Request $request)
    {
        $user = $this->ensureCourier();
        $q = trim($request->get('q',''));

        $deliveries = Delivery::with(['pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end'])
            ->forCourier($user->id)
            ->active()
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

    public function markInTransit(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        if (is_null($delivery->courier_id)) {
            $delivery->courier_id = $user->id;
        } elseif ($delivery->courier_id !== $user->id) {
            abort(403, 'This delivery is assigned to another courier.');
        }
        abort_if(!in_array($delivery->status, ['scheduled','assigned']), 422);

        $delivery->status       = 'in_transit';
        $delivery->picked_up_at = $delivery->picked_up_at ?? now();
        $delivery->save();

        return back()->with('ok', "Delivery #{$delivery->id} is now In transit.");
    }

    public function markDelivered(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        abort_if($delivery->courier_id !== $user->id, 403);
        abort_if($delivery->status !== 'in_transit', 422);

        $delivery->status         = 'delivered';
        $delivery->arrived_hub_at = now();
        $delivery->save();

        return back()->with('ok', "Delivery #{$delivery->id} delivered to hub.");
    }
// Le bouton "Select delivery" arrive ici
    public function claim(Request $request, Pickup $pickup)
    {
        $user = $this->ensureCourier();

        // déjà prise ?
        if (Delivery::where('pickup_id', $pickup->id)->exists()) {
            return back()->with('ok', "Pickup #{$pickup->id} est déjà liée à une delivery.");
        }

        DB::transaction(function () use ($pickup, $user) {
            // créer la delivery
            Delivery::create([
                'pickup_id'    => $pickup->id,
                'courier_id'   => $user->id,
                'courier_phone'=> $user->phone ?? null, // si tu as une colonne phone côté users
                'hub_address'  => config('recircle.hub.address', 'ReCircle Hub — 12 Rue Exemple, Tunis'),
                'hub_lat'      => config('recircle.hub.lat'),
                'hub_lng'      => config('recircle.hub.lng'),
                'status'       => 'assigned',
                'tracking_code'=> $pickup->tracking_code, // ou un nouveau code si tu préfères
            ]);

            // 🔴 mettre à jour la PICKUP aussi
            $pickup->update([
                'courier_id' => $user->id,
                'status'     => 'assigned',
            ]);
        });

        return redirect()->route('deliveries.index')->with('ok', "Delivery for pickup #{$pickup->id} created.");
    }
}


   /* public function claim(\App\Models\Delivery $delivery)
{
    $user = $this->ensureCourier();

    // déjà pris par un autre ?
    if (!is_null($delivery->courier_id) && $delivery->courier_id !== $user->id) {
        abort(409, 'This delivery is already assigned to another courier.');
    }

    // rattacher au courier courant
    $delivery->courier_id   = $user->id;
    // si tu veux stocker le téléphone à la volée :
    if (!$delivery->courier_phone && isset($user->phone)) {
        $delivery->courier_phone = $user->phone;
    }

    // scheduled -> assigned
    if ($delivery->status === 'scheduled') {
        $delivery->status      = 'assigned';
        $delivery->assigned_at = now();
    }

    $delivery->save();

    // sync aussi le pickup
    if ($delivery->relationLoaded('pickup')) {
        $delivery->pickup->update(['courier_id' => $user->id]);
    } else {
        optional($delivery->pickup)->update(['courier_id' => $user->id]);
    }

    return back()->with('ok', "Delivery #{$delivery->id} assigned to you.");
}*/



/*namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    private function ensureCourier()
    {
        $user = auth()->user();
        // adapte selon ton Enum/colonne de rôle
        abort_if(!$user || $user->role->value !== 'courier', 403);
        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->ensureCourier();
        $q = trim($request->get('q',''));

        $deliveries = Delivery::with(['pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end'])
            ->forCourier($user->id)
            ->active()
            ->when($q !== '', function ($b) use ($q) {
                $b->where('tracking_code', 'like', "%$q%")
                  ->orWhereHas('pickup.wasteItem', fn($w) => $w->where('title','like',"%$q%"))
                  ->orWhereHas('pickup', fn($p) => $p->where('pickup_address','like',"%$q%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
return view()->file(
    resource_path('views/courier/deliveries/index.blade.php'),
    compact('deliveries')
);
        return view('courier.deliveries.index', compact('deliveries'));
    }

    public function markInTransit(Delivery $delivery)
    {
        $user = $this->ensureCourier();

        // S’auto-assigner si non assignée
        if (is_null($delivery->courier_id)) {
            $delivery->courier_id = $user->id;
        } elseif ($delivery->courier_id !== $user->id) {
            abort(403, 'This delivery is assigned to another courier.');
        }

        // Autorisé uniquement depuis scheduled/assigned
        abort_if(!in_array($delivery->status, ['scheduled','assigned']), 422);

        $delivery->status       = 'in_transit';
        $delivery->picked_up_at = $delivery->picked_up_at ?? now();
        $delivery->save();

        return back()->with('ok', "Delivery #{$delivery->id} is now In transit.");
    }

    public function markDelivered(Delivery $delivery)
    {
        $user = $this->ensureCourier();
        abort_if($delivery->courier_id !== $user->id, 403);

        abort_if($delivery->status !== 'in_transit', 422);

        $delivery->status        = 'delivered';
        $delivery->arrived_hub_at= now();
        $delivery->save();

        return back()->with('ok', "Delivery #{$delivery->id} delivered to hub.");
    }
    
}*/