<?php

// app/Http/Controllers/VoteController.php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function __construct(private BadgeService $badgeService) {}

    public function vote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:up,down',
            'votable_type' => 'required|in:discussion,reply',
            'votable_id' => 'required|integer',
        ]);

        $votableClass = $validated['votable_type'] === 'discussion'
            ? \App\Models\ForumDiscussion::class
            : \App\Models\ForumReply::class;

        $votable = $votableClass::findOrFail($validated['votable_id']);

        DB::transaction(function () use ($votable, $validated) {
            // Remove existing vote
            $votable->votes()->where('user_id', auth()->id())->delete();

            // Add new vote
            if ($validated['type'] !== 'cancel') {
                $votable->votes()->create([
                    'user_id' => auth()->id(),
                    'type' => $validated['type'],
                ]);

                // If it's an upvote, update stats for the content author
                if ($validated['type'] === 'up' && $votable->user_id !== auth()->id()) {
                    $this->badgeService->updateUserStats($votable->user, 'like_received');
                    $this->badgeService->checkAndAwardBadges($votable->user, 'like_received');
                }
            }
        });

        return response()->json([
            'success' => true,
            'vote_score' => $votable->voteScore(),
            'user_vote' => $votable->userVote(auth()->user())?->type,
        ]);
    }
}
