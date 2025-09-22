<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class UserManagementController extends Controller
{
   public function index(Request $request)
{
    $query = User::query();
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    return view('admin.usersDashboard', compact('users'));
}


 public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:generator,maker,buyer,courier'],
            'confirm' => ['required', 'accepted'],
        ]);

        $user->update(['role' => $data['role']]);
        return redirect()->route('admin.users')->with('success', 'User role updated');
    }

 public function toggleStatus(Request $request, User $user)
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        $user->email_verified_at = $user->hasVerifiedEmail() ? null : now();
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User verification status changed');
    }

}
