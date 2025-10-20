<?php

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

        // Get the old role before updating
        $oldRole = $user->role->value;

        $user->update(['role' => $data['role']]);

        // Log the action
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'role_changed',
            'description' => "Changed role for {$user->name} from {$oldRole} to {$data['role']}",
            'ip_address' => $request->ip(),
            'metadata' => [
                'target_user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $data['role'],
            ],
        ]);

        return redirect()->route('admin.users')->with('success', 'User role updated');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        $oldStatus = $user->hasVerifiedEmail() ? 'verified' : 'unverified';

        $user->email_verified_at = $user->hasVerifiedEmail() ? null : now();
        $user->save();

        $newStatus = $user->hasVerifiedEmail() ? 'verified' : 'unverified';

        // Log the action
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'user_verified',
            'description' => "Changed verification status for {$user->name} from {$oldStatus} to {$newStatus}",
            'ip_address' => $request->ip(),
            'metadata' => [
                'target_user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);

        return redirect()->route('admin.users')->with('success', 'User verification status changed');
    }

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
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'user_blocked',
            'description' => "Blocked user: {$user->name} ({$user->email}) - Reason: {$data['block_reason']}",
            'ip_address' => $request->ip(),
            'metadata' => [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'block_reason' => $data['block_reason'],
            ],
        ]);

        return redirect()->route('admin.users')->with('success', 'User blocked successfully');
    }

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
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'user_unblocked',
            'description' => "Unblocked user: {$user->name} ({$user->email})",
            'ip_address' => $request->ip(),
            'metadata' => [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
            ],
        ]);

        return redirect()->route('admin.users')->with('success', 'User unblocked successfully');
    }
}
