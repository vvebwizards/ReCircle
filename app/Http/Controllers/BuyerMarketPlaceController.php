<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerMarketPlaceController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role->value !== 'buyer') {
            abort(403, 'Access denied. Buyers only.');
        }

        $type = $request->get('type', 'products');

        if ($type === 'products') {
            return $this->showProducts($request);
        } else {
            return $this->showMaterials($request);
        }
    }

    private function showProducts(Request $request)
    {
        $query = Product::where('status', 'published')
            ->with(['maker', 'images', 'materials']);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('category') && $request->category) {
            $query->whereHas('materials', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low': $query->orderBy('price', 'asc');
                break;
            case 'price_high': $query->orderBy('price', 'desc');
                break;
            case 'name_asc': $query->orderBy('name', 'asc');
                break;
            case 'name_desc': $query->orderBy('name', 'desc');
                break;
            case 'featured': $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default: $query->orderBy('created_at', 'desc');
        }

        $items = $query->paginate(12);

        $stats = [
            'total_items' => Product::where('status', 'published')->count(),
            'total_makers' => \App\Models\User::whereHas('products', function ($q) {
                $q->where('status', 'published');
            })->count(),
            'categories_count' => count(Material::CATEGORIES),
        ];

        return view('marketplace.buyerMarketPlace', [
            'items' => $items,
            'stats' => $stats,
            'type' => 'products',
        ]);
    }

    private function showMaterials(Request $request)
    {
        $query = Material::with(['maker', 'images', 'products'])
            ->where('quantity', '>', 0);

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('min_score') && $request->min_score) {
            $query->where('recyclability_score', '>=', $request->min_score);
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'score_high': $query->orderBy('recyclability_score', 'desc');
                break;
            case 'score_low': $query->orderBy('recyclability_score', 'asc');
                break;
            case 'quantity_high': $query->orderBy('quantity', 'desc');
                break;
            case 'name_asc': $query->orderBy('name', 'asc');
                break;
            case 'name_desc': $query->orderBy('name', 'desc');
                break;
            default: $query->orderBy('created_at', 'desc');
        }

        $items = $query->paginate(12);

        $stats = [
            'total_items' => Material::where('quantity', '>', 0)->count(),
            'average_score' => Material::where('quantity', '>', 0)->avg('recyclability_score') ?? 0,
            'categories_count' => Material::where('quantity', '>', 0)->distinct('category')->count('category'),
        ];

        return view('marketplace.buyerMarketPlace', [
            'items' => $items,
            'stats' => $stats,
            'type' => 'materials',
        ]);
    }

    public function show($type, $id)
    {
        if (Auth::user()->role->value !== 'buyer') {
            abort(403, 'Access denied. Buyers only.');
        }

        if ($type === 'product') {
            $item = Product::with(['maker', 'images', 'materials.images'])
                ->where('status', 'published')
                ->findOrFail($id);

            if (! $item->material_passport) {
                $item->generateMaterialPassport();
            }

            $relatedItems = Product::where('status', 'published')
                ->where('id', '!=', $item->id)
                ->whereHas('materials', function ($q) use ($item) {
                    $q->whereIn('category', $item->materials->pluck('category'));
                })
                ->with('images')
                ->limit(4)
                ->get();

            return view('marketplace.product-details', compact('item', 'relatedItems', 'type'));

        } else {
            $item = Material::with(['maker', 'images', 'products.images'])
                ->where('quantity', '>', 0)
                ->findOrFail($id);

            $relatedItems = Material::where('quantity', '>', 0)
                ->where('id', '!=', $item->id)
                ->where('category', $item->category)
                ->with('images')
                ->limit(4)
                ->get();

            return view('marketplace.material-details', compact('item', 'relatedItems', 'type'));
        }
    }
}
