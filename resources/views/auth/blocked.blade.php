{{-- resources/views/auth/blocked.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Account Blocked
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ $message ?? 'Your account has been temporarily suspended.' }}
            </p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-ban text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Account Access Restricted
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>If you believe this is a mistake, please contact our support team.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="{{ route('home') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                Return to Homepage
            </a>
        </div>
    </div>
</div>
@endsection