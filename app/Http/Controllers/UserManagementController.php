<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\User;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.usersDashboard', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:generator,maker,buyer,courier'],
            'confirm' => ['required', 'accepted'],
        ]);

        $user->update(['role' => $data['role']]);

        return redirect()->route('admin.users')->with('success', 'User role updated');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        $user->email_verified_at = $user->hasVerifiedEmail() ? null : now();
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User verification status changed');
    }
}
