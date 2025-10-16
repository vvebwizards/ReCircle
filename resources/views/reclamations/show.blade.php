{{-- resources/views/reclamations/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Reclamation Details')

@section('content')
<main class="container py-8" style="margin-top: 90px;">
    {{-- Back button --}}
    <div class="mb-6">
        <a href="{{ route('reclamations.index') }}" 
           class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to My Reclamations
        </a>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Reclamation Details Card --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-start">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $reclamation->topic }}</h1>
                <div class="mt-2 flex items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $reclamation->user->name }}
                    </span>
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $reclamation->created_at->format('F j, Y \a\t g:i A') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $reclamation->getStatusBadgeAttribute() }}">
                    {{ ucfirst(str_replace('_', ' ', $reclamation->status)) }}
                </span>
            </div>
        </div>

        {{-- Description --}}
        <div class="px-6 py-6 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Description</h2>
            <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $reclamation->description }}</p>
        </div>

        {{-- Action Buttons --}}
        <div class="px-6 py-4 bg-gray-50 flex gap-3">
            @if($reclamation->status === 'pending')
                <a href="{{ route('reclamations.edit', $reclamation) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            @endif

            <form action="{{ route('reclamations.destroy', $reclamation) }}" method="POST" class="inline-block"
                  onsubmit="return confirm('Are you sure you want to delete this reclamation?');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Responses Section --}}
    @if($reclamation->responses->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Responses ({{ $reclamation->responses->count() }})
            </h2>

            <div class="space-y-4">
                @foreach($reclamation->responses as $response)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $response->admin->name ?? 'Admin' }}
                                    </p>
                                    <p class="text-xs text-gray-500">Admin</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500">
                                {{ $response->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="pl-10">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $response->message }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-8 bg-gray-50 rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="mt-2 text-gray-600">No responses yet.</p>
            <p class="text-sm text-gray-500">An admin will respond to your reclamation soon.</p>
        </div>
    @endif
</main>
@endsection