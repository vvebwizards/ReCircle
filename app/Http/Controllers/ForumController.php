<?php

// app/Http/Controllers/ForumController.php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumDiscussion;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(): View
    {
        $categories = ForumCategory::active()
            ->withCount('discussions')
            ->with(['latestDiscussion.user'])
            ->orderBy('order')
            ->get();

        $recentDiscussions = ForumDiscussion::with(['user', 'category', 'lastReplier'])
            ->latest()
            ->take(10)
            ->get();

        $popularDiscussions = ForumDiscussion::with(['user', 'category'])
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();

        return view('forum.index', compact('categories', 'recentDiscussions', 'popularDiscussions'));
    }

    public function category(ForumCategory $category): View
    {
        $discussions = ForumDiscussion::with(['user', 'lastReplier'])
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(20);

        return view('forum.category', compact('category', 'discussions'));
    }

    public function show(ForumCategory $category, ForumDiscussion $discussion): View
    {
        // Check if discussion belongs to category
        if ($discussion->category_id !== $category->id) {
            abort(404);
        }

        // Increment view count
        $discussion->incrementViewCount();

        $replies = $discussion->replies()
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->get();

        return view('forum.discussion', compact('category', 'discussion', 'replies'));
    }
}
