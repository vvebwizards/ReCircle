{{-- resources/views/forum/create-discussion.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Discussion - ReCircle Forum')

@section('content')
<div class="container mx-auto px-4 py-8" style="padding-top: 100px; padding-bottom: 100px;">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h1 class="text-xl font-semibold text-gray-900">Create New Discussion</h1>
            </div>
            
            <form action="{{ route('forum.discussions.store') }}" method="POST" class="p-6">
                @csrf
                
                <!-- Category -->
                <div class="mb-6">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category_id" id="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', request('category')) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Discussion Title</label>
                    <input type="text" name="title" id="title" required 
                           value="{{ old('title') }}"
                           placeholder="Enter a clear and descriptive title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Discussion Content</label>
                    <textarea name="content" id="content" rows="12" required
                              placeholder="Describe your question, idea, or topic in detail. You can use Markdown formatting."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 text-sm text-gray-500">
                        <p>Tips for a great discussion:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Be clear and specific about your topic</li>
                            <li>Include relevant details about waste materials or processes</li>
                            <li>Ask specific questions to encourage responses</li>
                            <li>Use Markdown for formatting</li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <a href="{{ url()->previous() }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
<button type="submit" class="btn btn-primary">
    Create Discussion
</button>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection