<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class AdminDeliveryController extends Controller
{
    /** Vérifie que l’utilisateur est admin (adapte selon ton système de rôles) */
    private function ensureAdmin(): void
    {
        $u = auth()->user();
        abort_if(! $u || ($u->role->value ?? null) !== 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $q = trim($request->get('q', ''));
        $deliveries = Delivery::with(['pickup.wasteItem', 'courier'])
            ->when($q !== '', function ($b) use ($q) {
                $b->where('tracking_code', 'like', "%$q%")
                    ->orWhereHas('pickup', fn ($p) => $p->where('pickup_address', 'like', "%$q%"))
                    ->orWhereHas('pickup.wasteItem', fn ($w) => $w->where('title', 'like', "%$q%"));
            })
            ->whereIn('status', ['scheduled', 'assigned', 'in_transit'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // 👇 ajoute 'tab' ici
        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'tab' => 'active',
        ]);
    }

    public function completed(Request $request)
    {
        $this->ensureAdmin();

        $q = trim($request->get('q', ''));
        $deliveries = Delivery::with(['pickup.wasteItem', 'courier'])
            ->when($q !== '', function ($b) use ($q) {
                $b->where('tracking_code', 'like', "%$q%")
                    ->orWhereHas('pickup', fn ($p) => $p->where('pickup_address', 'like', "%$q%"))
                    ->orWhereHas('pickup.wasteItem', fn ($w) => $w->where('title', 'like', "%$q%"));
            })
            ->where('status', 'delivered')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // 👇 onglet "completed"
        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'tab' => 'completed',
        ]);
    }

    public function show(Delivery $delivery)
    {
        $this->ensureAdmin();
        $delivery->load(['pickup.wasteItem', 'courier']);

        return view('admin.deliveries.show', compact('delivery'));
    }

    public function edit(Delivery $delivery)
    {
        $this->ensureAdmin();
        $delivery->load(['pickup.wasteItem', 'courier']);

        // Statuts modifiables côté admin
        $statuses = ['scheduled', 'assigned', 'in_transit', 'delivered', 'failed', 'cancelled'];

        return view('admin.deliveries.edit', compact('delivery', 'statuses'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:scheduled,assigned,in_transit,delivered,failed,cancelled'],
            'courier_id' => ['nullable', 'integer', 'exists:users,id'],
            'courier_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
        ]);

        $delivery->fill($data);

        // Effets de bord
        if ($delivery->status === 'in_transit' && is_null($delivery->picked_up_at)) {
            $delivery->picked_up_at = now();
        }
        if ($delivery->status === 'delivered' && is_null($delivery->arrived_hub_at)) {
            $delivery->arrived_hub_at = now();
        }

        $delivery->save();

        // ✅ redirection selon le nouveau statut
        $dest = in_array($delivery->status, ['delivered', 'failed', 'cancelled'])
            ? route('admin.deliveries.completed')
            : route('admin.deliveries.index');

        return redirect($dest)->with('ok', "Delivery #{$delivery->id} updated.");
    }

    public function destroy(Delivery $delivery)
    {
        $this->ensureAdmin();

        $delivery->delete(); // soft delete si tu utilises SoftDeletes
        if (request()->expectsJson()) {
            return response()->noContent();
        }

        return back()->with('ok', "Delivery #{$delivery->id} deleted.");
    }
}
