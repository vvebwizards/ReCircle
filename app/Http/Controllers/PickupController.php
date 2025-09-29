<?php
namespace App\Http\Controllers;

use App\Models\Pickup;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function index()
    {
        $pickups = Pickup::latest()->get();
        return view('pickups.index', compact('pickups'));
    }

    public function edit(Pickup $pickup)
    {
        return view('pickups.edit', compact('pickup'));
    }

    public function update(Request $request, Pickup $pickup)
    {
        $data = $request->validate([
            'pickup_address' => 'required|string|max:255',
            'scheduled_pickup_window_start' => 'nullable|date',
            'scheduled_pickup_window_end'   => 'nullable|date|after_or_equal:scheduled_pickup_window_start',
            'status'        => 'required|in:scheduled,assigned,in_transit,picked,failed,cancelled',
            'notes'         => 'nullable|string',
        ]);

        $pickup->update($data);

        return redirect()->route('pickups.index')->with('ok','Pickup updated.');
    }

    public function destroy(Pickup $pickup)
    {
        $pickup->delete();
        return redirect()->route('pickups.index')->with('ok','Pickup deleted.');
    }
}