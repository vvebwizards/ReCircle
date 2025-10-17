<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\ReclamationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReclamationController extends Controller
{
    /**
     * Display a listing of all reclamations (Admin).
     */
    public function index(Request $request)
    {
        $query = Reclamation::with('user', 'responses');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('topic', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('email', 'like', '%'.$request->search.'%');
                    });
            });
        }

        // Sort by date (newest first by default)
        $reclamations = $query->latest()->paginate(15);

        // Get counts for status filters
        $statusCounts = [
            'all' => Reclamation::count(),
            'pending' => Reclamation::pending()->count(),
            'in_progress' => Reclamation::inProgress()->count(),
            'resolved' => Reclamation::resolved()->count(),
            'closed' => Reclamation::closed()->count(),
        ];

        return view('admin.reclamations.index', compact('reclamations', 'statusCounts'));
    }

    /**
     * Display the specified reclamation (Admin).
     */
    public function show(Reclamation $reclamation)
    {
        $reclamation->load('user', 'responses.admin');

        return view('admin.reclamations.show', compact('reclamation'));
    }

    /**
     * Store a response to a reclamation (Admin).
     */
    public function storeResponse(Request $request, Reclamation $reclamation)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'update_status' => 'nullable|in:pending,in_progress,resolved,closed',
        ]);

        // Create the response
        ReclamationResponse::create([
            'reclamation_id' => $reclamation->id,
            'admin_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        // Update reclamation status if specified
        if (isset($validated['update_status'])) {
            $reclamation->update(['status' => $validated['update_status']]);
        } else {
            // If no status specified and reclamation is pending, move to in_progress
            if ($reclamation->status === 'pending') {
                $reclamation->update(['status' => 'in_progress']);
            }
        }

        return redirect()->route('admin.reclamations.show', $reclamation)
            ->with('success', 'Response sent successfully!');
    }

    /**
     * Update reclamation status (Admin).
     */
    public function updateStatus(Request $request, Reclamation $reclamation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
        ]);

        $reclamation->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Status updated successfully!');
    }

    /**
     * Delete a reclamation (Admin).
     */
    public function destroy(Reclamation $reclamation)
    {
        $reclamation->delete();

        return redirect()->route('admin.reclamations.index')
            ->with('success', 'Reclamation deleted successfully!');
    }

    /**
     * Get pending reclamations count (API endpoint for badge).
     */
    public function pendingCount()
    {
        $count = Reclamation::pending()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Bulk actions (Admin).
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:mark_in_progress,mark_resolved,mark_closed,delete',
            'reclamation_ids' => 'required|array',
            'reclamation_ids.*' => 'exists:reclamations,id',
        ]);

        $reclamations = Reclamation::whereIn('id', $validated['reclamation_ids']);

        switch ($validated['action']) {
            case 'mark_in_progress':
                $reclamations->update(['status' => 'in_progress']);
                $message = 'Selected reclamations marked as in progress.';
                break;
            case 'mark_resolved':
                $reclamations->update(['status' => 'resolved']);
                $message = 'Selected reclamations marked as resolved.';
                break;
            case 'mark_closed':
                $reclamations->update(['status' => 'closed']);
                $message = 'Selected reclamations marked as closed.';
                break;
            case 'delete':
                $reclamations->delete();
                $message = 'Selected reclamations deleted.';
                break;
        }

        return redirect()->route('admin.reclamations.index')
            ->with('success', $message);
    }
}
