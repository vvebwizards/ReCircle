<?php

// app/Http/Controllers/AuditLogController.php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('admin')->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('admin', function ($adminQuery) use ($search) {
                        $adminQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date').' 00:00:00',
                $request->input('end_date').' 23:59:59',
            ]);
        }

        $auditLogs = $query->paginate(20)->withQueryString();

        // Get all admins for filter dropdown (only those who have performed actions)
        $admins = User::whereIn('id', function ($query) {
            $query->select('admin_id')->from('audit_logs')->distinct();
        })->get();

        // Get unique action types for filter dropdown
        $actionTypes = AuditLog::distinct()->pluck('action');

        // Add the missing variables for dashboard stats
        $totalLogs = AuditLog::count();
        $activeAdmins = User::whereIn('id', function ($query) {
            $query->select('admin_id')->from('audit_logs')->distinct();
        })->count();
        $criticalActions = AuditLog::whereIn('action', ['user_blocked', 'role_changed'])->count();

        return view('admin.audit-logs', compact(
            'auditLogs',
            'admins',
            'actionTypes',
            'totalLogs',
            'activeAdmins',
            'criticalActions'
        ));
    }

    // Make this method public so we can use it in the view
    public function getActionBadgeColor($action): string
    {
        return match ($action) {
            'user_blocked', 'user_unblocked' => 'security',
            'role_changed', 'user_verified' => 'user_management',
            'login', 'logout' => 'system',
            default => 'moderation'
        };
    }

    // Add the missing getActionIcon method
    public function getActionIcon($action): string
    {
        return match ($action) {
            'user_blocked' => 'fa-user-slash',
            'user_unblocked' => 'fa-user-check',
            'role_changed' => 'fa-user-gear',
            'user_verified' => 'fa-user-shield',
            'login' => 'fa-sign-in',
            'logout' => 'fa-sign-out',
            default => 'fa-circle-info'
        };
    }
}
