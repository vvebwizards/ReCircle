<?php

// app/Http/Controllers/AIController.php

namespace App\Http\Controllers;

use App\Models\ForumDiscussion;
use App\Models\ForumReply;
use App\Services\GeminiAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    private GeminiAIService $aiService;

    public function __construct(GeminiAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateReplySuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'discussion_id' => 'required|exists:forum_discussions,id',
        ]);

        try {
            $discussion = ForumDiscussion::findOrFail($request->discussion_id);

            $content = $discussion->content;
            $context = "Discussion: {$discussion->title}";

            $suggestions = $this->aiService->generateReplySuggestions($content, $context);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);

        } catch (\Exception $e) {
            \Log::error('Gemini Reply Suggestion Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function generateSummary(Request $request): JsonResponse
    {
        $request->validate([
            'discussion_id' => 'required|exists:forum_discussions,id',
        ]);

        try {
            $discussion = ForumDiscussion::with('replies')->findOrFail($request->discussion_id);

            $content = $discussion->content;
            $topReplies = $discussion->replies()
                ->orderBy('like_count', 'desc')
                ->take(3)
                ->get()
                ->pluck('content')
                ->implode("\n\n");

            $fullContent = "Discussion: {$discussion->title}\n\n{$content}\n\nKey Replies:\n{$topReplies}";

            $summary = $this->aiService->generateSummary($fullContent, $discussion->title);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);

        } catch (\Exception $e) {
            \Log::error('Gemini Summary Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function generateReplyToReply(Request $request): JsonResponse
    {
        $request->validate([
            'reply_id' => 'required|exists:forum_replies,id',
        ]);

        try {
            $reply = ForumReply::with('discussion')->findOrFail($request->reply_id);

            $context = "Replying to comment in: {$reply->discussion->title}";
            $suggestions = $this->aiService->generateReplySuggestions($reply->content, $context);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);

        } catch (\Exception $e) {
            \Log::error('Gemini Reply to Reply Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions.',
            ], 500);
        }
    }
}
