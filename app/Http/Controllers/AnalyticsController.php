<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Material;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function generateAnalyticsPDF()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $stats = $this->getUserStats($userId);

        $data = [
            'user' => $user,
            'stats' => $stats,
            'generation_date' => now()->format('F d, Y'),
            'period' => 'All Time',
        ];

        $pdf = PDF::loadView('maker.analytics-pdf', $data);

        $filename = "{$user->name}_Analytics_Report_".now()->format('Y_m_d').'.pdf';

        return $pdf->download($filename);
    }

    private function getUserStats($userId)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $materialCategories = Material::where('maker_id', $userId)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        $co2ByCategory = Material::where('maker_id', $userId)
            ->whereHas('products')
            ->select('category', DB::raw('SUM(co2_kg_saved) as total_co2'))
            ->groupBy('category')
            ->get()
            ->pluck('total_co2', 'category')
            ->toArray();

        $monthlyComparison = $this->getMonthlyComparison($userId);

        return [
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

            'monthly_products' => Product::where('maker_id', $userId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),

            'total_materials' => Material::where('maker_id', $userId)->count(),
            'material_stock' => Material::where('maker_id', $userId)->sum('quantity'),

            'material_categories' => $materialCategories,

            'waste_items_purchased' => 15,
            'waste_items_converted' => 8,

            'monthly_comparison' => $monthlyComparison,
            'co2_by_category' => $co2ByCategory,
        ];
    }

    private function getMonthlyComparison($userId)
    {
        $months = [];
        $currentDate = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $currentDate->copy()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthName = $date->format('M');

            $months[$monthKey] = [
                'name' => $monthName,
                'products' => Product::where('maker_id', $userId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'materials' => Material::where('maker_id', $userId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'co2_saved' => Material::where('maker_id', $userId)
                    ->whereHas('products')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('co2_kg_saved') ?? 0,
            ];
        }

        return $months;
    }

    public function index()
    {
        $userId = Auth::id();
        $stats = $this->getUserStats($userId);

        return view('maker.analytics', compact('stats'));
    }
}
