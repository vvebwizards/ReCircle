<?php

namespace App\Http\Controllers;

use App\Events\BidSubmitted;
use App\Http\Requests\StoreBidRequest;
use App\Http\Requests\UpdateBidRequest;
use App\Http\Requests\UpdateBidStatusRequest;
use App\Models\Bid;
use App\Models\WasteItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    // GET /waste-items/{wasteItem}/bids
    public function index(WasteItem $wasteItem): JsonResponse
    {
        $this->authorize('viewAny', [Bid::class, $wasteItem]);
        $bids = $wasteItem->bids()->with('maker:id,name')->latest()->paginate(15);

        return response()->json($bids);
    }

    // POST /waste-items/{wasteItem}/bids
    public function store(StoreBidRequest $request, WasteItem $wasteItem): JsonResponse
    {
        $this->authorize('create', [Bid::class, $wasteItem]);

        $data = $request->validated();

        $bid = $wasteItem->bids()->create([
            'maker_id' => $request->user()->id,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'notes' => $data['notes'] ?? null,
            'status' => Bid::STATUS_PENDING,
        ]);
        
        // Broadcast bid submitted event for real-time updates
        event(new BidSubmitted($bid));

        return response()->json($bid->load('maker:id,name'), 201);
    }

    // GET /bids/{bid}
    public function show(Bid $bid): JsonResponse
    {
        $this->authorize('view', $bid);

        return response()->json($bid->load('maker:id,name', 'wasteItem:id,title'));
    }

    // PATCH /bids/{bid}
    public function update(UpdateBidRequest $request, Bid $bid): JsonResponse
    {
        $this->authorize('update', $bid);

        if ($bid->status !== Bid::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending bids can be updated'], 422);
        }

        $data = $request->validated();

        $bid->fill($data);
        $bid->save();

        return response()->json($bid->fresh()->load('maker:id,name'));
    }

    // PATCH /bids/{bid}/status
    public function updateStatus(UpdateBidStatusRequest $request, Bid $bid): JsonResponse
    {
        $this->authorize('updateStatus', $bid);

        $data = $request->validated();

        if ($bid->status !== Bid::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending bids can transition'], 422);
        }

        if ($data['status'] === Bid::STATUS_ACCEPTED) {
            DB::transaction(function () use ($bid) {
                // accept target bid
                $bid->markAccepted();
                
                // Broadcast the accepted bid
                event(new BidSubmitted($bid));

                // set maker on the waste item to the accepted bid's maker
                $bid->wasteItem()->update(['maker_id' => $bid->maker_id]);

                // reject other pending bids on same waste item
                $bid->wasteItem
                    ->bids()
                    ->where('id', '!=', $bid->id)
                    ->where('status', Bid::STATUS_PENDING)
                    ->get()
                    ->each(function ($other) {
                        $other->markRejected();
                        // Broadcast each rejected bid
                        event(new BidSubmitted($other));
                    });
            });
        } else {
            $bid->markRejected();
            // Broadcast the rejected bid
            event(new BidSubmitted($bid));
        }

        return response()->json($bid->fresh()->load('maker:id,name'));
    }

    // PATCH /bids/{bid}/withdraw
    public function withdraw(Request $request, Bid $bid): JsonResponse
    {
        $this->authorize('withdraw', $bid);

        if ($bid->status !== Bid::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending bids can be withdrawn'], 422);
        }

        $bid->markWithdrawn();
        
        // Broadcast the withdrawn bid
        event(new BidSubmitted($bid));

        return response()->json($bid->fresh()->load('maker:id,name'));
    }
}
