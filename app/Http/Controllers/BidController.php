<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBidRequest;
use App\Http\Requests\UpdateBidRequest;
use App\Http\Requests\UpdateBidStatusRequest;
use App\Models\Bid;
use App\Models\WasteItem;
use Illuminate\Http\JsonResponse;
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

    public function updateStatus(\App\Http\Requests\UpdateBidStatusRequest $request, \App\Models\Bid $bid)
    {
        $this->authorize('updateStatus', $bid);

        $data = $request->validated();

        if ($bid->status !== \App\Models\Bid::STATUS_PENDING) {
            // déjà traité
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Only pending bids can transition'], 422);
            }

            return back()->with('error', 'Bid is not pending.');
        }

        if ($data['status'] === \App\Models\Bid::STATUS_ACCEPTED) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($bid) {
                // 1) accepter ce bid
                $bid->markAccepted();

                // 2) rejeter les autres en attente du même waste item
                $bid->wasteItem->bids()
                    ->where('id', '!=', $bid->id)
                    ->where('status', \App\Models\Bid::STATUS_PENDING)
                    ->update(['status' => \App\Models\Bid::STATUS_REJECTED]);
                /*->get()
                ->each(function ($other) {
                    $other->markRejected();*/
            });

            // 3) si ce n'est PAS une requête AJAX => on redirige vers le formulaire Pickup
            if (! $request->expectsJson()) {
                return redirect()->route('pickups.create', [
                    'waste_item_id' => $bid->waste_item_id,
                ])->with('ok', 'Bid accepted. Please schedule a pickup.');
            }

            return response()->json($bid->fresh()->load('maker:id,name'));
        }

        // Sinon : rejet
        $bid->markRejected();

        if ($request->expectsJson()) {
            return response()->json($bid->fresh()->load('maker:id,name'));
        }

        return back()->with('ok', 'Bid rejected.');
    }

    /*
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
                    // reject other pending bids on same waste item
                    $bid->wasteItem->bids()->where('id', '!=', $bid->id)->where('status', Bid::STATUS_PENDING)->get()->each(function ($other) {
                        $other->markRejected();
                    });
                });
            } else {
                $bid->markRejected();
            }

            return response()->json($bid->fresh()->load('maker:id,name'));
        }
    */
    // PATCH /bids/{bid}/withdraw
    public function withdraw(Request $request, Bid $bid): JsonResponse
    {
        $this->authorize('withdraw', $bid);

        if ($bid->status !== Bid::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending bids can be withdrawn'], 422);
        }

        $bid->markWithdrawn();

        return response()->json($bid->fresh()->load('maker:id,name'));
    }
}
