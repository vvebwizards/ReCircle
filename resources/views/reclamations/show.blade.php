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

    {{-- Add Reply Section --}}
    @if(!$reclamation->isClosed())
        <div class="mt-8 bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Your Reply</h2>
            <form action="{{ route('reclamation.user-reply.store', $reclamation) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        Your Message
                    </label>
                    <textarea 
                        id="message" 
                        name="message" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Type your reply or additional information here..."
                        required
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Conversation Thread --}}
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            Conversation History ({{ $reclamation->responses->count() + 1 }})
        </h2>

        <div class="space-y-6">
            {{-- All Responses (Admin + User Replies) --}}
            @foreach($reclamation->responses->sortBy('created_at') as $response)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 {{ $response->isFromUser() ? 'ml-8 bg-gray-50' : '' }}">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $response->isFromAdmin() ? 'bg-indigo-100' : 'bg-green-100' }} rounded-full flex items-center justify-center">
                                @if($response->isFromAdmin())
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    @if($response->isFromAdmin())
                                        {{ $response->admin->name ?? 'Admin' }}
                                    @else
                                        {{ $response->user->name ?? $reclamation->user->name }}
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">
                                    @if($response->isFromAdmin())
                                        Administrator
                                    @else
                                        Your Reply
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $response->created_at->format('M j, Y g:i A') }}
                        </span>
                    </div>
                    <div class="pl-13">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $response->message }}</p>
                    </div>
                </div>
            @endforeach

            {{-- No Responses Message --}}
            @if($reclamation->responses->count() === 0)
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="mt-2 text-gray-600">No responses yet.</p>
                    <p class="text-sm text-gray-500">An admin will respond to your reclamation soon.</p>
                </div>
            @endif
        </div>
    </div>
</main>
@endsection