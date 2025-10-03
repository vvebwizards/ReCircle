<?php

// app/Http/Controllers/UserManagementController.php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('blockedByUser');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->input('status') === 'blocked') {
                $query->blocked();
            } elseif ($request->input('status') === 'active') {
                $query->active();
            }
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

    // New method to block a user
    public function blockUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'block_reason' => ['required', 'string', 'max:500'],
            'confirm' => ['required', 'accepted'],
        ]);

        $user->update([
            'blocked_at' => now(),
            'block_reason' => $data['block_reason'],
            'blocked_by' => auth()->id(),
        ]);

        // Log the action
        // AuditLog::create([
        //     'user_id' => auth()->id(),
        //     'action' => 'user_blocked',
        //     'description' => "Blocked user: {$user->name} ({$user->email}) - Reason: {$data['block_reason']}",
        //     'ip_address' => $request->ip(),
        // ]);

        return redirect()->route('admin.users')->with('success', 'User blocked successfully');
    }

    // New method to unblock a user
    public function unblockUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        $user->update([
            'blocked_at' => null,
            'block_reason' => null,
            'blocked_by' => null,
        ]);

        // Log the action
        // AuditLog::create([
        //     'user_id' => auth()->id(),
        //     'action' => 'user_unblocked',
        //     'description' => "Unblocked user: {$user->name} ({$user->email})",
        //     'ip_address' => $request->ip(),
        // ]);

        return redirect()->route('admin.users')->with('success', 'User unblocked successfully');
    }
}
