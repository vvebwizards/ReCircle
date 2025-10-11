<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Material;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get material categories count
        $materialCategories = Material::where('maker_id', $userId)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        $stats = [
            'co2_saved' => Material::where('maker_id', $userId)
                ->whereHas('products')
                ->sum('co2_kg_saved') ?? 0,

            'landfill_avoided' => Material::where('maker_id', $userId)
                ->whereHas('products')
                ->sum('landfill_kg_avoided') ?? 0,

            'total_products' => Product::where('maker_id', $userId)->count(),
            'published_products' => Product::where('maker_id', $userId)
                ->where('status', ProductStatus::PUBLISHED)->count(),
            'draft_products' => Product::where('maker_id', $userId)
                ->where('status', ProductStatus::DRAFT)->count(),
            'sold_out_products' => Product::where('maker_id', $userId)
                ->where('status', ProductStatus::SOLD_OUT)->count(),

            'monthly_products' => Product::where('maker_id', $userId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),

            'total_materials' => Material::where('maker_id', $userId)->count(),
            'material_stock' => Material::where('maker_id', $userId)->sum('quantity'),

            // Material categories data
            'material_categories' => $materialCategories,

            'waste_items_purchased' => 15,
            'waste_items_converted' => 8,
        ];

        return view('maker.analytics', compact('stats'));
    }
}
