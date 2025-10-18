<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use App\Models\ReclamationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ReclamationController extends Controller
{
    /**
     * Display a listing of reclamations.
     */
    public function index()
    {
        $reclamations = Reclamation::with('user', 'responses.admin')
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

        $reclamation = new Reclamation;
        $reclamation->user_id = Auth::id();
        $reclamation->topic = $validated['topic'];
        $reclamation->description = $validated['description'];
        $reclamation->status = 'pending';

        // Call Python API for severity classification
        try {
            $response = Http::post('http://127.0.0.1:8001/classify', [
                'description' => $validated['description'],
            ]);
            $reclamation->severity = $response->json('severity'); // Changed from category to severity
        } catch (\Exception $e) {
            $reclamation->severity = 'medium'; // fallback to medium
        }

        $reclamation->save();

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Reclamation submitted successfully!');
    }

    /**
     * Display the specified reclamation.
     */
    public function show(Reclamation $reclamation)
    {
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
     * Display user's own reclamations.
     */
    public function myReclamations()
    {
        $reclamations = Reclamation::with('responses.admin')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('reclamations.my-reclamations', compact('reclamations'));
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

    /**
     * Store a user reply to their reclamation.
     */
    public function storeUserReply(Request $request, Reclamation $reclamation)
    {
        // Only the owner can reply
        if ($reclamation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Users cannot reply to closed reclamations
        if ($reclamation->isClosed()) {
            abort(403, 'Cannot reply to a closed reclamation.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5|max:2000',
        ]);

        // Create user reply with user_id
        ReclamationResponse::create([
            'reclamation_id' => $reclamation->id,
            'user_id' => Auth::id(), // Set the user_id
            'admin_id' => null, // Explicitly set to null
            'message' => $validated['message'],
        ]);

        // Update reclamation status to in_progress when user replies (if it was pending)
        if ($reclamation->isPending()) {
            $reclamation->update(['status' => 'in_progress']);
        }

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Your reply has been sent successfully!');
    }
}
