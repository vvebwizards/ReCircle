<?php

namespace App\Http\Controllers;

use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GeneratorWasteItemController extends Controller
{
    public function create(): View
    {
        return view('generator.create_waste_item');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'condition' => ['required', 'in:good,fixable,scrap'],
            'estimated_weight' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'location.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'condition' => $validated['condition'],
            'estimated_weight' => $validated['estimated_weight'] ?? null,
            'location' => ($validated['location.lat'] ?? null) !== null || ($validated['location.lng'] ?? null) !== null ? [
                'lat' => $validated['location.lat'] ?? null,
                'lng' => $validated['location.lng'] ?? null,
            ] : null,
            'notes' => $validated['notes'] ?? null,
            'generator_id' => Auth::id(),
        ];
        $wasteItem = WasteItem::create($payload);

        if ($request->hasFile('images')) {
            $order = 0;
            foreach ($request->file('images') as $uploaded) {
                if (! $uploaded->isValid()) {
                    continue;
                }
                $storedPath = $uploaded->store('images/waste-items', 'public'); // returns path relative to storage/app/public
                $relative = str_replace('\\', '/', $storedPath); // e.g. images/waste-items/xxx.png
                $wasteItem->photos()->create([
                    'image_path' => 'storage/'.ltrim($relative, '/'), // publicly accessible
                    'order' => $order++,
                ]);
            }
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            $wasteItem->load('photos');

            return response()->json([
                'message' => 'Created',
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

        return redirect()->route('generator.waste-items.index')
            ->with('success', 'Waste item "'.$wasteItem->title.'" created.');
    }

    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = WasteItem::with('photos')->where('generator_id', Auth::id());

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

        $wasteItems = $query->paginate(12);
        $conditionsCount = WasteItem::where('generator_id', Auth::id())
            ->selectRaw('`condition` as cond, COUNT(*) as aggregate')
            ->groupBy('cond')
            ->pluck('aggregate', 'cond');

        $total = $wasteItems->total();
        $avgWeight = WasteItem::where('generator_id', Auth::id())->avg('estimated_weight') ?? 0;

        if ($request->wantsJson()) {
            return response()->json([
                'data' => [
                    'items' => $wasteItems->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'title' => $w->title,
                            'condition' => $w->condition,
                            'estimated_weight' => $w->estimated_weight,
                            'notes' => $w->notes,
                            'created_at' => $w->created_at?->toISOString(),
                            'primary_image_url' => $w->primary_image_url,
                            'images_count' => $w->photos->count(),
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $wasteItems->currentPage(),
                        'last_page' => $wasteItems->lastPage(),
                        'per_page' => $wasteItems->perPage(),
                        'total' => $wasteItems->total(),
                        'next_page_url' => $wasteItems->nextPageUrl(),
                        'prev_page_url' => $wasteItems->previousPageUrl(),
                    ],
                    'stats' => [
                        'total' => $total,
                        'avgWeight' => round($avgWeight, 2),
                        'conditionsCount' => $conditionsCount,
                    ],
                ],
            ]);
        }

        return view('generator.waste_items', [
            'wasteItems' => $wasteItems,
            'conditionsCount' => $conditionsCount,
            'total' => $total,
            'avgWeight' => $avgWeight,
        ]);
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
