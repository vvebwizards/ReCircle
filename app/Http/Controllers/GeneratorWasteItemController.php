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
            'images.*' => ['string'],
            'location.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'condition' => $validated['condition'],
            'estimated_weight' => $validated['estimated_weight'] ?? null,
            'images' => $validated['images'] ?? [],
            'location' => isset($validated['location.lat']) ? [
                'lat' => $validated['location.lat'] ?? null,
                'lng' => $validated['location.lng'] ?? null,
            ] : null,
            'notes' => $validated['notes'] ?? null,
            'generator_id' => Auth::id(),
        ];

        $wasteItem = WasteItem::create($payload);

        return redirect()->route('generator.waste-items.index')
            ->with('success', 'Waste item "'.$wasteItem->title.'" created.');
    }

    public function index(Request $request): View
    {
        $query = WasteItem::where('generator_id', Auth::id());

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
}
