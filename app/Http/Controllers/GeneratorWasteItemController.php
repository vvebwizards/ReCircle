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

        return redirect()->route('generator.waste-items.index')
            ->with('success', 'Waste item "'.$wasteItem->title.'" created.');
    }

    public function index(Request $request): View
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
        $wasteItem->load('photos');

        return response()->json([
            'data' => [
                'id' => $wasteItem->id,
                'title' => $wasteItem->title,
                'condition' => $wasteItem->condition,
                'estimated_weight' => $wasteItem->estimated_weight,
                'notes' => $wasteItem->notes,
                'location' => $wasteItem->location,
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
        ]);
        $wasteItem->update($validated);

        return response()->json(['message' => 'Updated', 'data' => $wasteItem->only(['id', 'title', 'condition', 'estimated_weight', 'notes'])]);
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
