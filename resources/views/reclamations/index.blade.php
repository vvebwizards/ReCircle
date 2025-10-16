{{-- resources/views/reclamations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Reclamations')

@section('content')
<div class="container" style="margin-top: 120px; margin-bottom: 100px;">
    {{-- Header with title + action button --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-semibold text-gray-900">My Reclamations</h1>

        <a href="{{ route('reclamations.create') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            New Reclamation
        </a>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($reclamations->count())
        <ul class="space-y-4">
            @foreach ($reclamations as $r)
                {{-- Only show reclamations of the logged-in user --}}
                @if($r->user_id === auth()->id())
                    <li class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                        <div class="flex-1">
                            <a href="{{ route('reclamations.show', $r) }}"
                               class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $r->topic }}
                            </a>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ Str::limit($r->description, 200) }}
                            </p>

                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $r->getStatusBadgeAttribute() }}">
                                    {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                                </span>
                                
                                @if($r->responses->count() > 0)
                                    <span class="text-xs text-gray-500">
                                        • {{ $r->responses->count() }} {{ Str::plural('response', $r->responses->count()) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 self-start sm:self-center">
                            {{ $r->created_at->diffForHumans() }}
                        </div>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $reclamations->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-gray-600">You haven't created any reclamations yet.</p>
            <a href="{{ route('reclamations.create') }}" 
               class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 font-medium">
                Create your first reclamation
            </a>
        </div>
    @endif

    {{-- Helpful Tips & Information Section --}}
    <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-blue-900">Helpful Information</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- Status Guide --}}
            <div class="space-y-3">
                <h3 class="font-medium text-blue-800">Understanding Statuses</h3>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mt-1 flex-shrink-0"></span>
                        <span><strong>Pending:</strong> Your reclamation is waiting for admin review</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mt-1 flex-shrink-0"></span>
                        <span><strong>In Progress:</strong> An admin is currently working on your case</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mt-1 flex-shrink-0"></span>
                        <span><strong>Resolved:</strong> Your issue has been successfully resolved</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 bg-gray-500 rounded-full mt-1 flex-shrink-0"></span>
                        <span><strong>Closed:</strong> The reclamation has been concluded</span>
                    </li>
                </ul>
            </div>

            {{-- Tips --}}
            <div class="space-y-3">
                <h3 class="font-medium text-blue-800">Tips for Better Support</h3>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Be specific and detailed in your descriptions</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Check for admin responses regularly</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>You can reply to admin responses to provide more information</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Edit your reclamation while it's still in "Pending" status</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Response Time Info --}}
        <div class="mt-4 p-3 bg-blue-100 rounded-lg border border-blue-200">
            <div class="flex items-center gap-2 text-sm text-blue-800">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><strong>Typical Response Time:</strong> Our team usually responds within 24-48 hours during business days</span>
            </div>
        </div>
    </div>
</div>
@endsection
