{{-- resources/views/forum/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Forum - ReCircle')

@section('content')
<div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-8 mb-8 text-white shadow-2xl border border-emerald-400/20">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-4">ReCircle Community</h1>
            <p class="text-xl mb-6 max-w-2xl mx-auto text-emerald-100">Connect, share knowledge, and collaborate with waste transformation enthusiasts</p>
            <a href="{{ route('forum.discussions.create') }}" 
               class="inline-flex items-center px-8 py-4 bg-white text-emerald-600 text-lg font-semibold rounded-lg hover:bg-emerald-50 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                <i class="fa-solid fa-plus mr-3"></i>
                Start a Discussion
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-8">
            <!-- Quick Stats & Leaderboard Preview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Community Stats -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i class="fa-solid fa-chart-line mr-3 text-emerald-400"></i>
                        Community Stats
                    </h2>
                    <div class="space-y-3">
                        @php
                            $totalDiscussions = \App\Models\ForumDiscussion::count();
                            $totalUsers = \App\Models\User::count();
                            $totalReplies = \App\Models\ForumReply::count();
                            $activeToday = \App\Models\User::whereHas('stats', function($q) {
                                $q->whereDate('last_activity_at', today());
                            })->count();
                        @endphp
                        <div class="flex justify-between items-center py-2 border-b border-gray-700">
                            <span class="text-gray-300">Total Discussions</span>
                            <span class="font-bold text-emerald-400">{{ $totalDiscussions }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-700">
                            <span class="text-gray-300">Community Members</span>
                            <span class="font-bold text-green-400">{{ $totalUsers }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-700">
                            <span class="text-gray-300">Total Replies</span>
                            <span class="font-bold text-teal-400">{{ $totalReplies }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-300">Active Today</span>
                            <span class="font-bold text-amber-400">{{ $activeToday }}</span>
                        </div>
                    </div>
                </div>

                <!-- Top Contributors Preview -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fa-solid fa-trophy mr-3 text-amber-400"></i>
                            Top Contributors
                        </h2>
                        <a href="{{ route('badges.leaderboard') }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                            View All →
                        </a>
                    </div>
                    <div class="space-y-3">
                        @php
                            $topUsers = \App\Models\User::whereHas('stats')
                                ->with('stats')
                                ->withCount('badges')
                                ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
                                ->orderByDesc('user_stats.total_points')
                                ->select('users.*')
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @foreach($topUsers as $index => $user)
                            <div class="flex items-center justify-between p-3 hover:bg-gray-750 rounded-xl transition-all duration-200 group border border-transparent hover:border-amber-500/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-gray-900
                                        {{ $index === 0 ? 'bg-amber-400' : '' }}
                                        {{ $index === 1 ? 'bg-gray-400' : '' }}
                                        {{ $index === 2 ? 'bg-amber-600' : '' }}
                                        {{ $index > 2 ? 'bg-emerald-500' : '' }} transition-transform duration-200 group-hover:scale-110">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold transition-transform duration-200 group-hover:scale-110">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-white group-hover:text-amber-300 transition-colors duration-200">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->stats->total_points }} pts</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fa-solid fa-medal text-amber-400 text-sm transition-transform duration-200 group-hover:scale-110"></i>
                                    <span class="text-xs font-medium text-gray-300">{{ $user->badges_count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h2 class="text-2xl font-bold text-white">Forum Categories</h2>
                    <p class="text-gray-400 mt-1">Explore discussions by category</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($categories as $category)
                        <a href="{{ route('forum.category', $category) }}" 
                           class="group p-6 rounded-xl border-2 border-gray-700 hover:border-emerald-500/50 hover:bg-gray-750/50 transition-all duration-300 transform hover:scale-[1.02] backdrop-blur-sm">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform duration-300 shadow-lg"
                                     style="background-color: {{ $category->color }}30; color: {{ $category->color }}; border: 1px solid {{ $category->color }}20;">
                                    <i class="fa-solid {{ $category->icon }}"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-white group-hover:text-emerald-300 transition-colors duration-200 mb-2">
                                        {{ $category->name }}
                                    </h3>
                                    <p class="text-gray-400 text-sm mb-3">{{ $category->description }}</p>
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span class="bg-gray-700 px-2 py-1 rounded-full transition-colors duration-200 group-hover:bg-emerald-500/20 group-hover:text-emerald-300">
                                            {{ $category->discussions_count }} discussions
                                        </span>
                                        @if($category->latestDiscussion)
                                            <span class="text-xs truncate max-w-[120px] opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-emerald-300">
                                                Latest: {{ Str::limit($category->latestDiscussion->title, 20) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Recent Discussions & Badges Preview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Discussions -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white">Recent Discussions</h2>
                        <a href="{{ route('forum.index') }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                            View All →
                        </a>
                    </div>
                    <div class="divide-y divide-gray-700">
                        @foreach($recentDiscussions as $discussion)
                            <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group border-l-4 border-transparent hover:border-emerald-500">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
                                           class="text-lg font-semibold text-white group-hover:text-emerald-300 transition-colors duration-200 block mb-2">
                                            {{ $discussion->title }}
                                        </a>
                                        <div class="flex items-center space-x-4 text-sm text-gray-400">
                                            <span class="flex items-center">
                                                <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-xs font-medium text-gray-300 mr-2 transition-transform duration-200 group-hover:scale-110">
                                                    {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                                                </div>
                                                {{ $discussion->user->name }}
                                            </span>
                                            <span class="text-gray-600">•</span>
                                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                                            <span class="text-gray-600">•</span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-all duration-200 group-hover:scale-105 border" 
                                                  style="background-color: {{ $discussion->category->color }}20; color: {{ $discussion->category->color }}; border-color: {{ $discussion->category->color }}30;">
                                                {{ $discussion->category->name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-500 group-hover:text-gray-400 transition-colors duration-200">
                                        <span class="flex items-center">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                            {{ $discussion->view_count }}
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fa-solid fa-comment mr-1"></i>
                                            {{ $discussion->reply_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Featured Badges Preview -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
                    <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white">Featured Badges</h2>
                        <a href="{{ route('badges.index') }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                            View All →
                        </a>
                    </div>
                    <div class="p-6">
                        @php
                            $featuredBadges = \App\Models\Badge::withCount('users')
                                ->active()
                                ->orderBy('points', 'desc')
                                ->take(6)
                                ->get()
                                ->groupBy('type');
                        @endphp
                        
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($featuredBadges->flatten()->take(6) as $badge)
                                <div class="text-center group cursor-pointer transform hover:scale-105 transition-transform duration-300">
                                    <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-xl shadow-lg transition-all duration-300 group-hover:border-{{ $badge->type === 'gold' 
    ? 'yellow-400' 
    : ($badge->type === 'silver' 
        ? 'gray-400' 
        : ($badge->type === 'bronze' 
            ? 'amber-400' 
            : 'emerald-400')) }}
                                         style="background-color: {{ $badge->color }};"
                                         title="{{ $badge->name }} - {{ $badge->description }}">
                                        <i class="fa-solid {{ $badge->icon }}"></i>
                                    </div>
                                    <div class="text-sm font-medium text-white group-hover:text-amber-300 transition-colors duration-200">{{ $badge->name }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $badge->users_count }} earned</div>
                                    <div class="text-xs text-gray-500 mt-1 capitalize">{{ $badge->type }}</div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Badge Progress -->
                        @auth
                            <div class="mt-6 pt-6 border-t border-gray-700">
                                <h3 class="text-sm font-semibold text-white mb-3">Your Progress</h3>
                                @php
                                    $userBadgesCount = auth()->user()->badges->count();
                                    $totalBadges = \App\Models\Badge::active()->count();
                                    $progress = $totalBadges > 0 ? ($userBadgesCount / $totalBadges) * 100 : 0;
                                @endphp
                                <div class="flex items-center justify-between text-sm text-gray-400 mb-2">
                                    <span>{{ $userBadgesCount }} of {{ $totalBadges }} badges</span>
                                    <span>{{ number_format($progress, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-2 rounded-full transition-all duration-1000 ease-out" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('badges.user-profile', auth()->user()) }}" 
                                       class="inline-flex items-center text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                                        <i class="fa-solid fa-trophy mr-2"></i>
                                        View Your Achievements
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mt-6 pt-6 border-t border-gray-700 text-center">
                                <p class="text-sm text-gray-400 mb-3">Sign in to track your badge progress</p>
                                <a href="{{ route('auth') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                                    Sign In to Earn Badges
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-2xl border border-emerald-500/30">
                <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('forum.discussions.create') }}" 
                       class="flex items-center justify-between p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 transform hover:scale-105 group backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="fa-solid fa-plus mr-3"></i>
                            <span>New Discussion</span>
                        </div>
                        <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                    </a>
                    <a href="{{ route('badges.leaderboard') }}" 
                       class="flex items-center justify-between p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 transform hover:scale-105 group backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="fa-solid fa-trophy mr-3"></i>
                            <span>Leaderboard</span>
                        </div>
                        <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                    </a>
                    <a href="{{ route('badges.index') }}" 
                       class="flex items-center justify-between p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 transform hover:scale-105 group backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="fa-solid fa-medal mr-3"></i>
                            <span>All Badges</span>
                        </div>
                        <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                    </a>
                </div>
            </div>

            <!-- Popular Discussions -->
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h3 class="text-sm font-semibold text-white">Trending Now</h3>
                </div>
                <div class="divide-y divide-gray-700">
                    @foreach($popularDiscussions as $discussion)
                        <div class="px-4 py-3 hover:bg-gray-750/50 transition-all duration-200 group">
                            <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
                               class="text-sm font-medium text-white hover:text-emerald-300 transition-colors duration-200 block mb-1">
                                {{ Str::limit($discussion->title, 50) }}
                            </a>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="transition-colors duration-200 group-hover:text-gray-300">{{ $discussion->category->name }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="transition-colors duration-200 group-hover:text-gray-300">{{ $discussion->view_count }} views</span>
                                    <span class="text-gray-600">•</span>
                                    <span class="transition-colors duration-200 group-hover:text-gray-300">{{ $discussion->reply_count }} replies</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Community Guidelines -->
            <div class="bg-amber-900/30 rounded-2xl border border-amber-700/50 p-5 transition-all duration-300 hover:shadow-lg hover:border-amber-600/50 backdrop-blur-sm">
                <h4 class="text-sm font-semibold text-amber-300 mb-3 flex items-center">
                    <i class="fa-solid fa-recycle mr-2"></i>
                    Circular Economy Tips
                </h4>
                <ul class="text-xs text-amber-200 space-y-2">
                    <li class="flex items-start transition-transform duration-200 hover:translate-x-1">
                        <i class="fa-solid fa-leaf mt-1 mr-2 text-amber-400"></i>
                        <span>Focus on upcycling and repurposing materials</span>
                    </li>
                    <li class="flex items-start transition-transform duration-200 hover:translate-x-1">
                        <i class="fa-solid fa-weight-scale mt-1 mr-2 text-amber-400"></i>
                        <span>Include material types and quantities</span>
                    </li>
                    <li class="flex items-start transition-transform duration-200 hover:translate-x-1">
                        <i class="fa-solid fa-photo-film mt-1 mr-2 text-amber-400"></i>
                        <span>Share transformation process photos</span>
                    </li>
                    <li class="flex items-start transition-transform duration-200 hover:translate-x-1">
                        <i class="fa-solid fa-hands-helping mt-1 mr-2 text-amber-400"></i>
                        <span>Help others with waste reduction techniques</span>
                    </li>
                </ul>
            </div>

            <!-- Achievement of the Day -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white text-center shadow-2xl transition-all duration-300 hover:shadow-3xl transform hover:scale-105 border border-emerald-400/30">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl mx-auto mb-3 transition-transform duration-300 hover:rotate-12">
                    <i class="fa-solid fa-recycle"></i>
                </div>
                <h4 class="font-bold mb-2">Eco Warrior Spotlight</h4>
                <p class="text-sm opacity-90 mb-3">"Material Transformer" - Turn waste into valuable products</p>
                <div class="text-xs opacity-75">
                    Earn 150 points per transformation
                </div>
            </div>
        </div>
    </div>
</div>
@endsection