<?php
// app/Http/Controllers/DiscussionController.php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumDiscussion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function create(): View
    {
        $categories = ForumCategory::active()->get();
        return view('forum.create-discussion', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'category_id' => 'required|exists:forum_categories,id',
        ]);

        $discussion = DB::transaction(function () use ($validated, $request) {
            $discussion = ForumDiscussion::create([
                'title' => $validated['title'],
                'slug' => \Str::slug($validated['title']),
                'content' => $validated['content'],
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
            ]);

            // Update category's latest discussion
            $discussion->category->touch();

            return $discussion;
        });

        return redirect()->route('forum.discussion', [
            'category' => $discussion->category,
            'discussion' => $discussion
        ])->with('success', 'Discussion created successfully!');
    }
}