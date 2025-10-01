<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardBidController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $wasteItems = $user->wasteItems()
            ->whereHas('bids')
            ->with([
                'bids' => function ($q) {
                    $q->with('maker:id,name')
                        ->orderByDesc('amount')
                        ->orderBy('created_at');
                },
                'photos' => function ($q) {
                    $q->orderBy('order');
                },
            ])
            ->orderByDesc('updated_at')
            ->get();

        return view('dashboard.bids', [
            'wasteItems' => $wasteItems,
        ]);
    }
}
