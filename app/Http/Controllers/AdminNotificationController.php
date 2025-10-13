<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['jwt.auth', 'admin']);
    }

    /**
     * Display a listing of admin notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filters
        $status = $request->string('status')->toString() ?: 'all'; // all|unread|read
        $type = $request->string('type')->toString() ?: 'all';     // all|security|system
        $q = trim((string) $request->input('q', ''));
        $from = $request->input('from');
        $to = $request->input('to');

        $query = $user->notifications();

        // Status filter
        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        // Type filter
        if ($type === 'security') {
            $query->where('type', 'App\\Notifications\\FailedFacialVerificationNotification');
        } elseif ($type === 'system') {
            $query->where('type', '!=', 'App\\Notifications\\FailedFacialVerificationNotification');
        }

        // Search filter (by user name/email inside data JSON when present)
        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('data->user_email', 'like', "%{$q}%")
                    ->orWhere('data->user_name', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%");
            });
        }

        // Date range filter
        if ($from) {
            try {
                $fromDt = Carbon::parse($from)->startOfDay();
                $query->where('created_at', '>=', $fromDt);
            } catch (\Throwable $e) {
            }
        }
        if ($to) {
            try {
                $toDt = Carbon::parse($to)->endOfDay();
                $query->where('created_at', '<=', $toDt);
            } catch (\Throwable $e) {
            }
        }

        // Sorting - clear default order from relation then apply
        $sort = $request->string('sort')->toString() ?: 'newest'; // newest|oldest|unread_first
        $query->reorder();
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'unread_first') {
            // Put unread first then newest
            $query->orderByRaw('read_at IS NULL DESC')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 4 per page
        $notifications = $query->paginate(4)->withQueryString();

        $unreadTotal = $user->unreadNotifications()->count();

        $filters = [
            'status' => $status,
            'type' => $type,
            'q' => $q,
            'from' => $from,
            'to' => $to,
            'sort' => $sort,
        ];

        return view('admin.notifications.index', compact('notifications', 'unreadTotal', 'filters'));
    }

    /**
     * Display a specific notification
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);

        // Mark as read when viewed
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // If this is an AJAX request, return partial as HTML or JSON depending on Accept
        if ($request->ajax() || $request->expectsJson()) {
            $html = view('admin.notifications.partials.details', compact('notification'))->render();
            $accept = strtolower($request->header('accept', ''));
            if (strpos($accept, 'text/html') !== false) {
                return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
            }

            return response()->json(['html' => $html]);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification deleted');
    }

    /**
     * Get unread notification count (AJAX endpoint)
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }
}
