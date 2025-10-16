{{-- resources/views/reclamations/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Reclamation')

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

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create New Reclamation</h1>
        <p class="mt-2 text-sm text-gray-600">Submit a new reclamation and our team will review it as soon as possible.</p>
    </div>

    {{-- Reclamation Form --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
        <form action="{{ route('reclamations.store') }}" method="POST">
            @csrf

            {{-- Topic Field --}}
            <div class="mb-6">
                <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">
                    Topic <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="topic" 
                       id="topic" 
                       value="{{ old('topic') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('topic') border-red-500 @enderror"
                       placeholder="Brief summary of your reclamation"
                       required>
                @error('topic')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description Field --}}
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                          placeholder="Please provide detailed information about your reclamation (minimum 10 characters)"
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                <a href="{{ route('reclamations.index') }}" 
                   class="inline-flex justify-center items-center px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex justify-center items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Submit Reclamation
                </button>
            </div>
        </form>
    </div>

    {{-- Help Section --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-900 mb-1">Tips for a good reclamation</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>Be clear and concise in your topic</li>
                    <li>Provide detailed information in the description</li>
                    <li>Include any relevant dates, order numbers, or references</li>
                    <li>Stay respectful and professional</li>
                </ul>
            </div>
        </div>
    </div>
</main>
@endsection