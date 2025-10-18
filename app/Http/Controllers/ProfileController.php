<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->loadCount(['followers', 'following', 'badges']);
        
        $isFollowing = auth()->check() && auth()->user()->isFollowing($user);
        
        // Get user's recent discussions
        $recentDiscussions = $user->discussions()
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        // Get user's recent replies
        $recentReplies = $user->replies()
            ->with('discussion.category')
            ->latest()
            ->take(5)
            ->get();

        return view('profiles.show', compact(
            'user', 
            'isFollowing', 
            'recentDiscussions', 
            'recentReplies'
        ));
    }

    public function followers(User $user): View
    {
        $followers = $user->followers()
            ->withCount(['badges', 'followers'])
            ->with('stats')
            ->paginate(20);

        return view('profiles.followers', compact('user', 'followers'));
    }

    public function following(User $user): View
    {
        $following = $user->following()
            ->withCount(['badges', 'followers'])
            ->with('stats')
            ->paginate(20);

        return view('profiles.following', compact('user', 'following'));
    }

    public function follow(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        auth()->user()->follow($user);

        return back()->with('success', "You are now following {$user->name}.");
    }

    public function unfollow(User $user): RedirectResponse
    {
        auth()->user()->unfollow($user);

        return back()->with('success', "You have unfollowed {$user->name}.");
    }

    public function activity(User $user): View
    {
        // Get discussions with pagination
        $discussions = $user->discussions()
            ->with('category')
            ->latest()
            ->paginate(10, ['*'], 'discussions_page')
            ->setPageName('discussions_page');

        // Get replies with pagination
        $replies = $user->replies()
            ->with(['discussion.category'])
            ->latest()
            ->paginate(10, ['*'], 'replies_page')
            ->setPageName('replies_page');

        return view('profiles.activity', compact('user', 'discussions', 'replies'));
    }

        // Alternative approach: Combined activity with manual pagination
    public function activityCombined(User $user): View
    {
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        
        // Get all activities (discussions and replies)
        $discussions = $user->discussions()
            ->with('category')
            ->get()
            ->map(function ($discussion) {
                return (object) [
                    'type' => 'discussion',
                    'item' => $discussion,
                    'created_at' => $discussion->created_at,
                ];
            });

        $replies = $user->replies()
            ->with(['discussion.category'])
            ->get()
            ->map(function ($reply) {
                return (object) [
                    'type' => 'reply',
                    'item' => $reply,
                    'created_at' => $reply->created_at,
                ];
            });

        // Combine and sort
        $allActivities = $discussions->concat($replies)
            ->sortByDesc('created_at');

        // Manual pagination
        $total = $allActivities->count();
        $activities = $allActivities->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Create custom paginator
        $activities = new \Illuminate\Pagination\LengthAwarePaginator(
            $activities,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('profiles.activity', compact('user', 'activities'));
    }
}