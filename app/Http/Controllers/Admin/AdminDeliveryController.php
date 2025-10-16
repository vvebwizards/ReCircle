<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class AdminDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q', '');

        $deliveries = Delivery::with([
                'pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end,tracking_code',
                'pickup.wasteItem:id,title',
                'courier:id,name,email', // si relation définie
            ])
            ->active()
            ->search($q)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'tab'        => 'active',
            'q'          => $q,
        ]);
    }

    public function completed(Request $request)
    {
        $q = $request->get('q', '');

        $deliveries = Delivery::with([
                'pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end,tracking_code',
                'pickup.wasteItem:id,title',
                'courier:id,name,email',
            ])
            ->completed()
            ->search($q)
            ->latest('arrived_hub_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'tab'        => 'completed',
            'q'          => $q,
        ]);
    }

    public function show(Delivery $delivery)
    {
        $delivery->load([
            'pickup:id,waste_item_id,pickup_address,scheduled_pickup_window_start,scheduled_pickup_window_end,tracking_code',
            'pickup.wasteItem:id,title',
            'courier:id,name,email',
        ]);

        return view('admin.deliveries.show', compact('delivery'));
    }
}