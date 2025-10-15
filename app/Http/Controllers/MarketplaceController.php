<?php

namespace App\Http\Controllers;

use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = WasteItem::with('photos')
                ->whereNull('maker_id'); // Only show waste items that aren't assigned to a maker

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
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

        $wasteItems = $query->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'grid' => view('marketplace.partials.grid', compact('wasteItems'))->render(),
                'pagination' => view('marketplace.partials.pagination', compact('wasteItems'))->render(),
            ]);
        }

        return view('marketplace.index', compact('wasteItems'));
    }

    public function show(WasteItem $wasteItem)
    {
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
                'primary_image_url' => $wasteItem->primary_image_url,
                'images' => $wasteItem->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => asset($p->image_path),
                    'order' => $p->order,
                ]),
            ],
        ]);
    }
}
