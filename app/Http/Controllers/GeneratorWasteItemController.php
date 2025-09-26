<?php

namespace App\Http\Controllers;

use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GeneratorWasteItemController extends Controller
{
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
