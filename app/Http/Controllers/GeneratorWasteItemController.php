<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteItemRequest;
use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GeneratorWasteItemController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = WasteItem::with(['photos', 'tags'])
            ->where('generator_id', Auth::id())
            ->whereNull('maker_id'); // Only show waste items that aren't assigned to a maker

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag);
            });
        }

        switch ($request->get('sort', 'newest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title_asc':
                $query->orderBy('title');
                break;
            case 'title_desc':
                $query->orderByDesc('title');
                break;
            default:
                $query->latest();
        }

        // Clone the base query BEFORE pagination for accurate aggregates across all pages
        $aggregateQuery = clone $query;
        $wasteItems = $query->paginate(12)->withQueryString();

        // Get statistics (total already available from paginator)
        $total = $wasteItems->total();
        // Average only over non-null, positive weights to avoid skew / division by zero issues
        $avgWeight = (float) $aggregateQuery->whereNotNull('estimated_weight')
            ->where('estimated_weight', '>', 0)
            ->avg('estimated_weight');
        $avgWeight = $avgWeight ?: 0; // ensure numeric 0 fallback
        $conditionsCount = WasteItem::where('generator_id', Auth::id())
            ->where('deleted_at', null)
            ->distinct('condition')
            ->count('condition');

        if ($request->ajax()) {
            return response()->json([
                'grid' => view('generator.partials.waste_items_grid', compact('wasteItems'))->render(),
                'stats' => view('generator.partials.waste_items_stats', compact('total', 'avgWeight', 'conditionsCount'))->render(),
                'pagination' => view('generator.partials.waste_items_pagination', compact('wasteItems'))->render(),
            ]);
        }

        return view('generator.waste_items', compact('wasteItems', 'total', 'avgWeight', 'conditionsCount'));
    }

    public function create(): View
    {
        return view('generator.create_waste_item');
    }

    public function store(StoreWasteItemRequest $request)
    {
        Log::info('[WasteItem] Store method called', ['user_id' => Auth::id(), 'request' => $request->all()]);
        $data = $request->validated();
        $data['generator_id'] = Auth::id();

        // images handled separately; remove to avoid mass assignment issue
        $images = $request->file('images');
        unset($data['images']);

        $wasteItem = WasteItem::create($data);

        // Object detection and auto-tagging
        $manualTags = [];
        if ($request->filled('tags')) {
            $manualTags = explode(',', $request->input('tags'));
            $manualTags = array_map('trim', $manualTags);
            $manualTags = array_filter($manualTags);
        }

        $objectDetectionService = app(\App\Services\ObjectDetectionService::class);

        if ($images) {
            $order = 0;
            // Detect materials from the first image
            $firstImage = $images[0];
            $detectedMaterials = null;
            if ($firstImage->isValid()) {
                \Log::info('[ObjectDetection] About to call ML service', ['image_name' => $firstImage->getClientOriginalName()]);
                try {
                    $detectedMaterials = $objectDetectionService->detectMaterials($firstImage);
                    \Log::info('[ObjectDetection] Raw ML response', ['detectedMaterials' => $detectedMaterials]);
                } catch (\Exception $e) {
                    \Log::error('[ObjectDetection] ML service exception', ['error' => $e->getMessage()]);
                    $detectedMaterials = null;
                }
            }
            // Store all images
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
            // Process detected materials as tags
            if ($detectedMaterials) {
                $materialTags = $objectDetectionService->materialsToTags($detectedMaterials);
                \Log::info('[ObjectDetection] Material tags', ['tags' => $materialTags]);
                foreach ($materialTags as $tag) {
                    \Log::info('[ObjectDetection] Attaching tag', ['tag' => $tag['name'], 'confidence' => $tag['confidence']]);
                    $wasteItem->attachTags([$tag['name']], true, $tag['confidence']);
                }
            }
        }

        // Add manual tags if any
        if (! empty($manualTags)) {
            $wasteItem->attachTags($manualTags);
        }

        // If no auto tags were attached, add debug info and optionally a debug tag to help diagnose
        $debugInfo = [
            'detectedMaterials' => $detectedMaterials ?? null,
        ];

        // If there were no detected materials at all, debug tagging is disabled in this environment

        if ($request->wantsJson() || $request->expectsJson()) {
            $wasteItem->load('photos');

            return response()->json([
                'message' => 'Created',
                'debug' => $debugInfo,
                'data' => [
                    'id' => $wasteItem->id,
                    'title' => $wasteItem->title,
                    'condition' => $wasteItem->condition,
                    'estimated_weight' => $wasteItem->estimated_weight,
                    'notes' => $wasteItem->notes,
                    'location' => $wasteItem->location,
                    'images' => $wasteItem->photos->map(fn ($p) => [
                        'id' => $p->id,
                        'url' => asset($p->image_path),
                        'order' => $p->order,
                    ]),
                    'primary_image_url' => $wasteItem->primary_image_url,
                ],
            ], 201);
        }

        // For non-AJAX requests, flash debug info to the session so it shows on the index page
        return redirect()->route('generator.waste-items.index')
            ->with('success', 'Waste item "'.$wasteItem->title.'" created.')
            ->with('object_detection_debug', $debugInfo);
    }

    public function show(WasteItem $wasteItem)
    {
        $this->ensureOwnership($wasteItem);
        $wasteItem->loadCount('materials');
        $wasteItem->load('photos');

        return response()->json([
            'data' => [
                'id' => $wasteItem->id,
                'title' => $wasteItem->title,
                'condition' => $wasteItem->condition,
                'estimated_weight' => $wasteItem->estimated_weight,
                'notes' => $wasteItem->notes,
                'location' => $wasteItem->location,
                'created_at' => $wasteItem->created_at?->toISOString(),
                'updated_at' => $wasteItem->updated_at?->toISOString(),
                'materials_count' => $wasteItem->materials_count,
                'primary_image_url' => $wasteItem->primary_image_url,
                'images' => $wasteItem->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'path' => $p->image_path,
                    'url' => asset($p->image_path),
                    'order' => $p->order,
                ]),
            ],
        ]);
    }

    public function update(Request $request, WasteItem $wasteItem)
    {
        $this->ensureOwnership($wasteItem);
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'condition' => ['sometimes', 'required', 'in:good,fixable,scrap'],
            'estimated_weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'location.lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'keep_images' => ['sometimes', 'nullable', 'string'], // CSV ids in final order
            'remove_images' => ['sometimes', 'nullable', 'string'], // CSV ids to delete
            'new_images.*' => ['sometimes', 'image', 'max:2048'],
        ]);
        // Extract structured location from bracket style if provided
        if ($request->has('location')) {
            $loc = $request->input('location');
            $validated['location'] = [
                'lat' => $loc['lat'] ?? null,
                'lng' => $loc['lng'] ?? null,
            ];
        }

        // Basic attribute update
        $wasteItem->update(collect($validated)->only(['title', 'condition', 'estimated_weight', 'notes', 'location'])->toArray());

        // Handle images
        $keepIds = collect(explode(',', (string) $request->input('keep_images')))->filter()->map(fn ($v) => (int) $v)->values();
        $removeIds = collect(explode(',', (string) $request->input('remove_images')))->filter()->map(fn ($v) => (int) $v)->values();

        if ($removeIds->isNotEmpty()) {
            $wasteItem->photos()->whereIn('id', $removeIds)->get()->each(function ($img) {
                // Optionally unlink file
                if ($img->image_path && file_exists(public_path($img->image_path))) {
                    @unlink(public_path($img->image_path));
                }
                $img->delete();
            });
        }
        // Reorder kept images
        if ($keepIds->isNotEmpty()) {
            foreach ($keepIds as $idx => $id) {
                $wasteItem->photos()->where('id', $id)->update(['order' => $idx]);
            }
        }

        // Add new images
        if ($request->hasFile('new_images')) {
            $currentCount = $wasteItem->photos()->count();
            foreach ($request->file('new_images') as $idx => $file) {
                $filename = uniqid('waste_').'.'.$file->getClientOriginalExtension();
                $path = 'storage/images/waste-items/'.$filename;
                $file->move(public_path('storage/images/waste-items'), $filename);
                $wasteItem->photos()->create([
                    'image_path' => $path,
                    'order' => $currentCount + $idx,
                ]);
            }
        }

        $wasteItem->load('photos');

        return response()->json(['message' => 'Updated', 'data' => [
            'id' => $wasteItem->id,
            'title' => $wasteItem->title,
            'condition' => $wasteItem->condition,
            'estimated_weight' => $wasteItem->estimated_weight,
            'notes' => $wasteItem->notes,
            'location' => $wasteItem->location,
            'images' => $wasteItem->photos->map(fn ($p) => [
                'id' => $p->id,
                'url' => asset($p->image_path),
                'order' => $p->order,
            ]),
        ]]);
    }

    public function destroy(WasteItem $wasteItem)
    {
        $this->ensureOwnership($wasteItem);
        $wasteItem->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function ensureOwnership(WasteItem $wasteItem): void
    {
        if ((int) $wasteItem->generator_id !== (int) Auth::id()) {
            abort(403, 'Not allowed');
        }
    }
}
