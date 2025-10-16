<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use App\Models\ReclamationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReclamationResponseController extends Controller
{
    /**
     * Display a listing of responses (Admin only).
     */
    public function index()
    {
        $responses = ReclamationResponse::with('reclamation.user', 'admin')
            ->latest()
            ->paginate(15);

        return view('reclamation-responses.index', compact('responses'));
    }

    /**
     * Store a new response (Admin only).
     */
    public function store(Request $request, Reclamation $reclamation)
    {
        $validated = $request->validate([
            'response_message' => 'required|string|min:10',
            'status' => 'required|in:pending,resolved,rejected',
        ]);

        $response = ReclamationResponse::create([
            'reclamation_id' => $reclamation->id,
            'admin_id' => Auth::id(),
            'response_message' => $validated['response_message'],
            'status' => $validated['status'],
        ]);

        // Update reclamation status based on response
        $reclamationStatus = match ($validated['status']) {
            'resolved' => 'resolved',
            'rejected' => 'closed',
            default => 'in_progress',
        };

        $reclamation->update(['status' => $reclamationStatus]);

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Response sent successfully!');
    }

    /**
     * Display the specified response.
     */
    public function show(ReclamationResponse $response)
    {
        $response->load('reclamation.user', 'admin');

        return view('reclamation-responses.show', compact('response'));
    }

    /**
     * Show the form for editing the specified response.
     */
    public function edit(ReclamationResponse $response)
    {
        // Only the admin who created it can edit
        if ($response->admin_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('reclamation-responses.edit', compact('response'));
    }

    /**
     * Update the specified response.
     */
    public function update(Request $request, ReclamationResponse $response)
    {
        // Only the admin who created it can update
        if ($response->admin_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'response_message' => 'required|string|min:10',
            'status' => 'required|in:pending,resolved,rejected',
        ]);

        $response->update($validated);

        // Update reclamation status based on response
        $reclamationStatus = match ($validated['status']) {
            'resolved' => 'resolved',
            'rejected' => 'closed',
            default => 'in_progress',
        };

        $response->reclamation->update(['status' => $reclamationStatus]);

        return redirect()->route('reclamations.show', $response->reclamation)
            ->with('success', 'Response updated successfully!');
    }

    /**
     * Remove the specified response.
     */
    public function destroy(ReclamationResponse $response)
    {
        // Only the admin who created it can delete
        if ($response->admin_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reclamation = $response->reclamation;
        $response->delete();

        return redirect()->route('reclamations.show', $reclamation)
            ->with('success', 'Response deleted successfully!');
    }
}
