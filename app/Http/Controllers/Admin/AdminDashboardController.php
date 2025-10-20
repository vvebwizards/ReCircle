<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Reclamation;
use App\Models\User;
use App\Models\WasteItem;
use Illuminate\Http\Request;

final class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard with aggregated stats.
     */
    public function index(Request $request)
    {
        // Users
        $usersCount = User::count();

        // Listings (waste items)
        $listingsCount = WasteItem::count();

        // Pending reclamations (flags)
        $pendingReclamations = Reclamation::where('status', 'pending')->count();

        // CO2 saved (sum of material co2_kg_saved)
        $co2Saved = (float) Material::whereNotNull('co2_kg_saved')->sum('co2_kg_saved');

        // Recent users (avoid selecting columns that may not exist in all installations)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return view(
            'admin.dashboard',
            compact('usersCount', 'listingsCount', 'pendingReclamations', 'co2Saved', 'recentUsers')
        );
    }
}
