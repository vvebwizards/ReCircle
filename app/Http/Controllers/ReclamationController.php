<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReclamationController extends Controller
{
    /**
     * Display a listing of reclamations (only authenticated user's reclamations).
     */
    public function index()
    {
        $reclamations = Reclamation::with('responses.admin')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('reclamations.index', compact('reclamations'));
    }

    /**
     * Show the form for creating a new reclamation.
     */
    public function create()
    {
        return view('reclamations.create');
    }

    /**
     * Store a newly created reclamation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'required|string|min:10',
        ]);

        $reclamation = Reclamation::create([
            'user_id' => Auth::id(),
            'topic' => $validated['topic'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Reclamation submitted successfully!');
    }

    /**
     * Display the specified reclamation.
     */
    public function show(Reclamation $reclamation)
    {
        // Only the owner can view their own reclamation
        if ($reclamation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reclamation->load('user', 'responses.admin');

        return view('reclamations.show', compact('reclamation'));
    }

    /**
     * Show the form for editing the specified reclamation.
     */
    public function edit(Reclamation $reclamation)
    {
        // Only the owner can edit
        if ($reclamation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('reclamations.edit', compact('reclamation'));
    }

    /**
     * Update the specified reclamation.
     */
    public function update(Request $request, Reclamation $reclamation)
    {
        // Only the owner can update
        if ($reclamation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'description' => 'required|string|min:10',
        ]);

        $reclamation->update($validated);

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Reclamation updated successfully!');
    }

    /**
     * Remove the specified reclamation.
     */
    public function destroy(Reclamation $reclamation)
    {
        // Only the owner can delete
        if ($reclamation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reclamation->delete();

        return redirect()->route('reclamations.index')
            ->with('success', 'Reclamation deleted successfully!');
    }

    /**
     * Update reclamation status (Admin only).
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
}
