<?php

namespace App\Http\Controllers;

use App\Models\WasteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MakerCollectionController extends Controller
{
    public function index(Request $request): View
    {
        $query = WasteItem::with('photos', 'generator:id,name')
            ->where('maker_id', Auth::id())
            ->latest();

        if ($search = $request->get('search')) {
            $searchBy = $request->get('search_by', 'title');

            switch ($searchBy) {
                case 'description':
                    $query->where('description', 'like', "%{$search}%");
                    break;
                case 'generator':
                    $query->whereHas('generator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    break;
                case 'title':
                default:
                    $query->where('title', 'like', "%{$search}%");
                    break;
            }
        }

        if ($condition = $request->get('condition')) {
            $query->where('condition', $condition);
        }

        $items = $query->paginate(12)->withQueryString();

        return view('maker.collection', compact('items'));
    }

    public function images(WasteItem $wasteItem)
    {
        abort_unless((int) $wasteItem->maker_id === (int) Auth::id(), 403);
        $wasteItem->load('photos');

        return response()->json([
            'data' => [
                'id' => $wasteItem->id,
                'images' => $wasteItem->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'path' => $p->image_path,
                    'url' => asset($p->image_path),
                    'order' => $p->order,
                ]),
            ],
        ]);
    }
}
