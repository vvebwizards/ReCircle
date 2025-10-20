<?php

namespace App\Http\Controllers;

use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWasteItemController extends Controller
{
    /**
     * Display listings page (Blade) or JSON list when requested via AJAX.
     */
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = WasteItem::with('photos', 'generator');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($condition = $request->get('condition')) {
            $query->where('condition', $condition);
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

        $items = $query->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'items' => $items->map(fn ($w) => [
                        'id' => $w->id,
                        'title' => $w->title,
                        'condition' => $w->condition,
                        'generator' => $w->generator?->name,
                        'estimated_weight' => $w->estimated_weight,
                        'created_at' => $w->created_at?->toISOString(),
                        'primary_image_url' => $w->primary_image_url,
                        'images_count' => $w->photos->count(),
                    ]),
                    'pagination' => [
                        'current_page' => $items->currentPage(),
                        'last_page' => $items->lastPage(),
                        'per_page' => $items->perPage(),
                        'total' => $items->total(),
                        'next_page_url' => $items->nextPageUrl(),
                        'prev_page_url' => $items->previousPageUrl(),
                    ],
                ],
            ]);
        }

        return view('admin.listings', [
            'items' => $items,
        ]);
    }

    /**
     * Detailed JSON for a single listing.
     */
    public function show(WasteItem $wasteItem)
    {
        $wasteItem->loadCount('materials')->load('photos', 'generator');

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
                'generator' => [
                    'id' => $wasteItem->generator?->id,
                    'name' => $wasteItem->generator?->name,
                    'email' => $wasteItem->generator?->email,
                ],
                'images' => $wasteItem->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => asset($p->image_path),
                    'order' => $p->order,
                ]),
                'primary_image_url' => $wasteItem->primary_image_url,
            ],
        ]);
    }

    /**
     * Update listing (no ownership restrictions for admins).
     */
    public function update(Request $request, WasteItem $wasteItem)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'condition' => ['sometimes', 'required', 'in:good,fixable,scrap'],
            'estimated_weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'location.lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'keep_images' => ['sometimes', 'nullable', 'string'],
            'remove_images' => ['sometimes', 'nullable', 'string'],
            'new_images.*' => ['sometimes', 'image', 'max:2048'],
        ]);

        if ($request->has('location')) {
            $loc = $request->input('location');
            $validated['location'] = [
                'lat' => $loc['lat'] ?? null,
                'lng' => $loc['lng'] ?? null,
            ];
        }

        $wasteItem->update(collect($validated)->only(['title', 'condition', 'estimated_weight', 'notes', 'location'])->toArray());

        $keepIds = collect(explode(',', (string) $request->input('keep_images')))->filter()->map(fn ($v) => (int) $v)->values();
        $removeIds = collect(explode(',', (string) $request->input('remove_images')))->filter()->map(fn ($v) => (int) $v)->values();

        if ($removeIds->isNotEmpty()) {
            $wasteItem->photos()->whereIn('id', $removeIds)->get()->each(function ($img) {
                if ($img->image_path && file_exists(public_path($img->image_path))) {
                    @unlink(public_path($img->image_path));
                }
                $img->delete();
            });
        }
        if ($keepIds->isNotEmpty()) {
            foreach ($keepIds as $idx => $id) {
                $wasteItem->photos()->where('id', $id)->update(['order' => $idx]);
            }
        }
        if ($request->hasFile('new_images')) {
            $currentCount = $wasteItem->photos()->count();
            foreach ($request->file('new_images') as $idx => $file) {
                $filename = uniqid('waste_').'.'.$file->getClientOriginalExtension();
                $dir = public_path('storage/images/waste-items');
                if (! is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                $file->move($dir, $filename);
                $wasteItem->photos()->create([
                    'image_path' => 'storage/images/waste-items/'.$filename,
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
            'primary_image_url' => $wasteItem->primary_image_url,
        ]]);
    }

    /**
     * Soft delete listing.
     */
    public function destroy(WasteItem $wasteItem)
    {
        $wasteItem->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
