<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ReCircle - Circular Economy Marketplace')</title>
    <meta name="description" content="@yield('meta_description', 'A marketplace where waste is transformed into products with measurable impact.')" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind for utility classes (via CDN to match template). You can remove if you compile Tailwind locally. -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css','resources/css/style.css'])
    @endunless
    <script>
        window.appRoutes = {
            home: @json(route('home')),
            auth: @json(route('auth')),
            twofa: @json(route('twofa')),
            forgot: @json(route('forgot-password')),
            dashboard: @auth
                @if(auth()->user()->role == \App\Enums\UserRole::MAKER)
                    @json(route('maker.dashboard', [], false))
                @elseif(auth()->user()->role == \App\Enums\UserRole::ADMIN)
                    @json(route('admin.dashboard', [], false))
                @else
                    @json(route('dashboard', [], false))
                @endif
            @else
                @json(route('dashboard', [], false))
            @endauth,
            settingsSecurity: @json(route('settings.security', [], false)),
            reclamationsCreate: @json(route('reclamations.create', [], false)),
        };
    </script>
    @unless (app()->environment('testing'))
        @vite(['resources/js/app.js','resources/js/main.js'])
    @endunless
    
    @auth
    <script>
        function handleLogout() {
            // Clear JWT token from localStorage
            localStorage.removeItem('auth_token');
            
            // Redirect to home page
            window.location.href = '{{ route("home") }}';
        }
    </script>
    @endauth

    @auth
    <script>
        console.log('TESTING: NEW CODE IS LOADED!!!');
        // Make current user data available to JavaScript with fresh data from database
        @php
            $freshUser = \App\Models\User::find(auth()->id());
            // Explicitly create the user object with avatar field
            $userData = [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'avatar' => $freshUser->avatar,
                'role' => $freshUser->role,
            ];
        @endphp
        window.__currentUser = @json($userData);
        console.log('NEW DEBUG: Fresh user data loaded:', window.__currentUser);
        console.log('NEW DEBUG: Avatar field specifically:', window.__currentUser.avatar);
        console.log('NEW DEBUG: User has avatar?', !!(window.__currentUser && window.__currentUser.avatar));
    </script>
    @endauth
    
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">
    {{-- Navbar (shared) --}}
    <nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <h2>
                @auth
                    <span class="nav-link" style="text-decoration:none;cursor:default;">ReCircle</span>
                @else
                    <a href="{{ route('home') }}" class="nav-link" style="text-decoration:none;">ReCircle</a>
                @endauth
            </h2>
        </div>
        <ul class="nav-menu">
            @auth
                <li class="nav-item">
                    <a href="{{ route('forum.index') }}" class="nav-link">
                        <i class="fa-solid fa-comments mr-1"></i>
                        Community Forum
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('messages.index') }}" class="nav-link">
                        <i class="fa-solid fa-envelope mr-1"></i>
                        Messages
                        @if(auth()->user()->unreadMessagesCount() > 0)
                            <span class="message-badge">
                                {{ auth()->user()->unreadMessagesCount() }}
                            </span>
                        @endif
                    </a>
                </li>
                                    <li class="nav-item">
                        @if(auth()->check() && auth()->user()->role->value === 'courier')
                        <a href="{{ route('courier.map') }}" class="nav-link">
                            <i class="fa-solid fa-map mr-1"></i>
                            Maps
                        </a>
                        @endif
                    </li>
                    <li class="nav-item">
                    @if(auth()->check() && auth()->user()->role->value === 'courier')
                    <a href="{{ route('deliveries.index') }}" class="nav-link">Deliveries</a>
                    @endif
                    </li>
            @else
                {{-- Links for guest users --}}
                <li class="nav-item"><a href="{{ route('home') }}#home" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#roles" class="nav-link">Roles</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#impact" class="nav-link">Impact</a></li>
                <li class="nav-item">
                    <a href="{{ route('forum.index') }}" class="nav-link">
                        <i class="fa-solid fa-comments mr-1"></i>
                        Community Forum
                    </a>
                </li>
                <li class="nav-item"><a href="{{ route('auth') }}" class="nav-cta" aria-label="Sign in">Sign In</a></li>
            @endauth
        </ul>
        <div class="hamburger" aria-label="Toggle navigation" aria-expanded="false" role="button" tabindex="0">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>
</nav>

    {{-- Page content --}}
    @yield('content')

    {{-- Footer (shared) --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ReCircle</h3>
                    <p>Connecting waste generators with local makers to create a transparent, impactful circular economy marketplace.</p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="social-link" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="social-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="social-link" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ route('home') }}#home">Home</a></li>
                        <li><a href="{{ route('home') }}#how-it-works">How It Works</a></li>
                        <li><a href="{{ route('home') }}#roles">Roles</a></li>
                        <li><a href="{{ route('home') }}#impact">Impact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Platform Features</h4>
                    <ul>
                        <li><a href="#">Waste Listings</a></li>
                        <li><a href="#">Bidding System</a></li>
                        <li><a href="#">Impact Tracking</a></li>
                        <li><a href="#">Material Passports</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Newsletter</h4>
                    <p>Get updates on new features and impact stories</p>
                    <form class="newsletter">
                        <input type="email" placeholder="Your email">
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} ReCircle. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </div>
    </footer>

    @stack('modals')
    @stack('scripts')
    <script>
        // Provide a helper for JS modules to get CSRF
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
    {{-- Flash Messages --}}
@if(session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <div class="flex items-center">
            <i class="fa-solid fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <div class="flex items-center">
            <i class="fa-solid fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    </div>
@endif

<script>
    // Auto-hide flash messages
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.fixed');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 3000);
        });
    });
</script>
</body>
</html>
