<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MakerBidController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Bid::query()
            ->with(['wasteItem:id,title', 'wasteItem.photos' => function ($q) {
                $q->orderBy('order');
            }])
            ->where('maker_id', $user->id);

        // Filters
        $status = $request->string('status')->trim();
        if ($status && in_array($status, [Bid::STATUS_PENDING, Bid::STATUS_ACCEPTED, Bid::STATUS_REJECTED, Bid::STATUS_WITHDRAWN])) {
            $query->where('status', $status);
        }

        $wasteTitle = $request->string('waste')->trim();
        if ($wasteTitle) {
            $query->whereHas('wasteItem', function ($q) use ($wasteTitle) {
                $q->where('title', 'like', '%'.$wasteTitle.'%');
            });
        }

        $from = $request->date('from');
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        $to = $request->date('to');
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $min = $request->input('min_amount');
        if (is_numeric($min)) {
            $query->where('amount', '>=', $min);
        }
        $max = $request->input('max_amount');
        if (is_numeric($max)) {
            $query->where('amount', '<=', $max);
        }

        // Default sort: newest then highest amount
        $query->orderByDesc('created_at')->orderByDesc('amount');

        $bids = $query->paginate(15)->withQueryString();

        return view('dashboard.maker-bids', [
            'bids' => $bids,
            'filters' => [
                'status' => $status,
                'waste' => $wasteTitle,
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
                'min_amount' => $min,
                'max_amount' => $max,
            ],
        ]);
    }
}
