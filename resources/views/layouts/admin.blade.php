<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin – ReCircle')</title>
    <meta name="description" content="@yield('meta_description', 'Administration panel for ReCircle platform.')" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css','resources/css/style.css','resources/js/app.js','resources/js/main.js', 'resources/css/admin.css','resources/js/admin-dashboard.js'])
    @endunless
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
@stack('admin-modals')
@stack('admin-scripts')
@unless (app()->environment('testing'))
    @vite(['resources/js/admin-dashboard.js','resources/js/admin-users-dashboard.js'])
@endunless
</body>
</html>
