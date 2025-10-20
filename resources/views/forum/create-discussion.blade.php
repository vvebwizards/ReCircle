{{-- resources/views/forum/create-discussion.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Discussion - ReCircle Forum')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c;">
    <div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
        <div class="max-w-4xl mx-auto">
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h1 class="text-xl font-semibold text-white">Create New Discussion</h1>
                </div>
                
                <form action="{{ route('forum.discussions.store') }}" method="POST" class="p-6">
                    @csrf
                    
                    <!-- Category -->
                    <div class="mb-6">
                        <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                        <select name="category_id" id="category_id" required
                                class="w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-700 text-white transition-all duration-200">
                            <option value="" class="text-gray-400">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('category_id', request('category')) == $category->id ? 'selected' : '' }}
                                        class="text-white">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Discussion Title</label>
                        <input type="text" name="title" id="title" required 
                               value="{{ old('title') }}"
                               placeholder="Enter a clear and descriptive title"
                               class="w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-700 text-white placeholder-gray-400 transition-all duration-200">
                        @error('title')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div class="mb-6">
                        <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Discussion Content</label>
                        <textarea name="content" id="content" rows="12" required
                                  placeholder="Describe your question, idea, or topic in detail. You can use Markdown formatting."
                                  class="w-full px-3 py-2 border border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-700 text-white placeholder-gray-400 transition-all duration-200">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 text-sm text-gray-400">
                            <p>Tips for a great discussion:</p>
                            <ul class="list-disc list-inside space-y-1 mt-1">
                                <li>Be clear and specific about your topic</li>
                                <li>Include relevant details about waste materials or processes</li>
                                <li>Ask specific questions to encourage responses</li>
                                <li>Use Markdown for formatting</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-700">
                        <a href="{{ url()->previous() }}" 
                           class="px-4 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 transform hover:scale-105">
                            Create Discussion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection