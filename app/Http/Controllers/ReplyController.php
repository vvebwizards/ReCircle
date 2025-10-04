<?php
// app/Http/Controllers/ReplyController.php

namespace App\Http\Controllers;

use App\Models\ForumDiscussion;
use App\Models\ForumReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    public function store(Request $request, ForumDiscussion $discussion): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|min:5',
            'parent_id' => 'nullable|exists:forum_replies,id',
        ]);

        DB::transaction(function () use ($validated, $discussion, $request) {
            $reply = ForumReply::create([
                'content' => $validated['content'],
                'user_id' => auth()->id(),
                'discussion_id' => $discussion->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'depth' => $validated['parent_id'] ? 
                    (ForumReply::find($validated['parent_id'])->depth + 1) : 0,
            ]);

            // Update discussion reply count and last reply
            $discussion->updateReplyCount();
        });

        return redirect()->route('forum.discussion', [
            'category' => $discussion->category,
            'discussion' => $discussion
        ])->with('success', 'Reply added successfully!');
    }

    public function markAsAnswer(ForumReply $reply): RedirectResponse
    {
        // Only discussion owner can mark as answer
        if ($reply->discussion->user_id !== auth()->id()) {
            abort(403);
        }

        // Remove answer from other replies in this discussion
        ForumReply::where('discussion_id', $reply->discussion_id)
            ->where('id', '!=', $reply->id)
            ->update(['is_answer' => false]);

        // Mark this reply as answer
        $reply->update(['is_answer' => true]);

        return back()->with('success', 'Reply marked as solution!');
    }
}