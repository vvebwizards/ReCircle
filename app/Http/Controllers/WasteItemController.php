<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteItemRequest;
use App\Http\Requests\UpdateWasteItemRequest;
use App\Models\WasteItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class WasteItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WasteItem::with('photos')
            ->whereNull('maker_id'); // Only show waste items that aren't assigned to a maker

        if ($request->filled('condition')) {
            $query->where('condition', $request->string('condition'));
        }
        if ($request->filled('generator_id')) {
            $query->where('generator_id', $request->integer('generator_id'));
        }
        if ($search = $request->string('search')->toString()) {
            $query->where('title', 'like', "%{$search}%");
        }

        $perPage = (int) min(100, $request->integer('per_page', 15));
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn ($w) => $this->transform($w))->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreWasteItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['generator_id'] = $request->user()->id;

        $images = $request->file('images');
        unset($data['images']);

        $wasteItem = WasteItem::create($data);

        if ($images) {
            $order = 0;
            foreach ($images as $uploaded) {
                if (! $uploaded->isValid()) {
                    continue;
                }
                $storedPath = $uploaded->store('images/waste-items', 'public');
                $relative = str_replace('\\', '/', $storedPath);
                $wasteItem->photos()->create([
                    'image_path' => 'storage/'.ltrim($relative, '/'),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json([
            'data' => $this->transform($wasteItem->fresh()),
        ], 201);
    }

    public function show(WasteItem $wasteItem): JsonResponse
    {
        return response()->json([
            'data' => $this->transform($wasteItem),
        ]);
    }

    public function update(UpdateWasteItemRequest $request, WasteItem $wasteItem): JsonResponse
    {
        $this->authorizeOwnership($request, $wasteItem);
        $data = $request->validated();

        $incomingImages = $request->file('images');
        unset($data['images']);
        $wasteItem->update($data);
        if ($incomingImages) {
            // remove existing image records and recreate (simpler approach)
            $wasteItem->photos()->delete();
            $order = 0;
            foreach ($incomingImages as $uploaded) {
                if (! $uploaded->isValid()) {
                    continue;
                }
                $storedPath = $uploaded->store('images/waste-items', 'public');
                $relative = str_replace('\\', '/', $storedPath);
                $wasteItem->photos()->create([
                    'image_path' => 'storage/'.ltrim($relative, '/'),
                    'order' => $order++,
                ]);
            }
        }

        return response()->json([
            'data' => $this->transform($wasteItem->fresh()),
        ]);
    }

    public function destroy(WasteItem $wasteItem): JsonResponse
    {
        $this->authorizeOwnership(request(), $wasteItem);
        $wasteItem->delete();

        return response()->json(status: 204);
    }

    private function transform(WasteItem $wasteItem): array
    {
        return [
            'id' => $wasteItem->id,
            'title' => $wasteItem->title,
            'images' => $wasteItem->photos->map(fn ($p) => $p->image_path)->all(),
            'primary_image' => $wasteItem->primary_image,
            'estimated_weight' => $wasteItem->estimated_weight,
            'condition' => $wasteItem->condition,
            'location' => $wasteItem->location,
            'notes' => $wasteItem->notes,
            'generator_id' => $wasteItem->generator_id,
            'created_at' => $wasteItem->created_at?->toIso8601String(),
            'updated_at' => $wasteItem->updated_at?->toIso8601String(),
        ];
    }

    private function authorizeOwnership(Request $request, WasteItem $wasteItem): void
    {
        if ((int) $request->user()->id !== (int) $wasteItem->generator_id) {
            abort(403, 'You do not own this waste item.');
        }
    }
}
