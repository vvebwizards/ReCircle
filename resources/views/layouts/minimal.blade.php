<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'ReCircle')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite(['resources/css/app.css','resources/css/style.css'])
  @stack('head')
</head>
<body>
  @yield('content')
  @stack('scripts')
  <script>window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');</script>
</body>
</html>
