<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin – ReCircle')</title>
    <meta name="description" content="@yield('meta_description', 'Administration panel for ReCircle platform.')" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @if (app()->environment('production'))
        @vite(['resources/css/app.css','resources/css/style.css','resources/js/app.js','resources/js/main.js'])
    @endif
    <script>
        window.appRoutes = {
            home: @json(route('home')),
            auth: @json(route('auth')),
            dashboard: @json(route('dashboard')),
            twofa: @json(route('twofa')),
            forgot: @json(route('forgot-password')),
            adminDashboard: @json(route('admin.dashboard')),
        };
    </script>
    @stack('admin-head')
</head>
<body class="admin-body">
<div id="admin-layout" class="layout-admin">
    @include('admin.partials.sidebar')
    <main class="admin-main">
        @yield('admin-content')
    </main>
</div>
@stack('admin-scripts')
@if (app()->environment('production'))
    @vite(['resources/js/admin-dashboard.js'])
@endif
</body>
</html>
