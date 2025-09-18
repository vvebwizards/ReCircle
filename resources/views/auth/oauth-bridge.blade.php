@extends('layouts.app')

@section('content')
<main class="container" style="padding:64px 0;">
  <div class="mx-auto max-w-md text-center">
    <h1 class="text-2xl font-semibold mb-2">Signing you in…</h1>
    <p class="text-gray-600 mb-6">Finishing up your sign-in and redirecting to your dashboard.</p>
    <div class="animate-pulse text-gray-400 mb-8">
      <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
    </div>
    <p class="text-sm text-gray-500">If you are not redirected automatically, <a href="{{ $redirectTo }}" class="text-blue-600 underline">click here</a>.</p>
  </div>
</main>
@endsection

@push('scripts')
<script>
  // Give the browser a tick to commit the Set-Cookie from this 200 response, then redirect.
  setTimeout(() => { window.location.replace(@json($redirectTo)); }, 50);
</script>
@endpush
