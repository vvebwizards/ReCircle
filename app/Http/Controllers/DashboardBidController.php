<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Bid;
use Illuminate\Support\Facades\DB;

class DashboardBidController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get filters
        $filters = [
            'status' => $request->get('status', ''),
            'title' => $request->get('title', ''),
            'from' => $request->get('from', ''),
            'to' => $request->get('to', ''),
            'min_amount' => $request->get('min_amount', ''),
            'max_amount' => $request->get('max_amount', ''),
        ];

        // Build query
        $query = $user->wasteItems()->whereHas('bids');

        // Apply title filter at query level
        if (! empty($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        // Get waste items with bids
        $wasteItems = $query->with([
            'bids' => function ($q) use ($filters) {
                $q->with('maker:id,name');

                // Apply bid-level filters
                if (! empty($filters['status']) && in_array($filters['status'], ['pending', 'accepted', 'rejected', 'withdrawn'])) {
                    $q->where('status', $filters['status']);
                }
                if (is_numeric($filters['min_amount'])) {
                    $q->where('amount', '>=', $filters['min_amount']);
                }
                if (is_numeric($filters['max_amount'])) {
                    $q->where('amount', '<=', $filters['max_amount']);
                }
                if (! empty($filters['from'])) {
                    $q->whereDate('created_at', '>=', $filters['from']);
                }
                if (! empty($filters['to'])) {
                    $q->whereDate('created_at', '<=', $filters['to']);
                }

                $q->orderByDesc('amount')->orderBy('created_at');
            },
            'photos',
        ])->get();

        // Remove waste items that have no matching bids after filtering
        if (array_filter($filters)) {
            $wasteItems = $wasteItems->filter(function ($item) {
                return $item->bids->isNotEmpty();
            })->values();
        }

        return view('dashboard.bids', [
            'wasteItems' => $wasteItems,
            'filters' => $filters,
        ]);
    }
/*
    //bid accept function
    public function accept(Request $request, Bid $bid)
{
    // Tu peux mettre une policy si tu veux : $this->authorize('accept', $bid);

    DB::transaction(function () use ($bid) {
        // 1) Accepter ce bid
        $bid->status = 'accepted';
        $bid->save();

        // 2) Rejeter tous les autres bids en attente du même waste item
        Bid::where('waste_item_id', $bid->waste_item_id)
            ->where('id', '!=', $bid->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);
    });

    // 3) Rediriger vers le formulaire Pickup en pré-sélectionnant l’article
    return redirect()->route('pickups.create', [
        'waste_item_id' => $bid->waste_item_id,
    ])->with('ok', 'Bid accepted. Please schedule a pickup.');
}*/

}
