<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class LeadNoteController extends Controller
{
    /**
     * Store a new note for a specific lead.
     */
    public function store(Request $request, Lead $lead)
    {
        // 1. Validate 'note' is not empty and fits within TEXT column limits
        $validated = $request->validate([
            'note' => 'required|string|max:65535',
        ], [
            'note.required' => 'Oops! You forgot to write the note body.',
            'note.max' => 'The note is way too long for our database.',
        ]);

        try {
            // 2. Attempt to create the note
            $note = LeadNote::create([
                'lead_id' => $lead->id,            // From Route Model Binding
                'user_id' => Auth::id() ?? 1,      // Fallback for your testing scenario
                'note'    => $validated['note'],
            ]);

            // 3. Return the created note on success
            return response()->json($note, 201);

        } catch (Exception $e) {
            // 4. Handle database or unexpected errors
            // Log the actual error so you can debug it later
            Log::error("Failed to create lead note: " . $e->getMessage());

            return response()->json([
                'error' => 'Could not save the note.',
                'message' => 'An unexpected database error occurred. Please try again later.'
            ], 500);
        }
    }
}
