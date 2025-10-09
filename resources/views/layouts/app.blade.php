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
            dashboard: @json(route('dashboard', [], false)),
            settingsSecurity: @json(route('settings.security', [], false)),
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
<body>
    <div style="background: red; color: white; padding: 10px; text-align: center; font-size: 20px;">
        TEMPLATE UPDATE TEST - IF YOU SEE THIS, THE TEMPLATE IS LOADING
    </div>
    {{-- Navbar (shared) --}}
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2><a href="{{ route('home') }}" class="nav-link" style="text-decoration:none;">ReCircle</a></h2>
            </div>
            
            @auth
                {{-- Authenticated Navigation --}}
                <ul class="nav-menu">
                    <li class="nav-item"><a href="{{ route('home') }}" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#roles" class="nav-link">Roles</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#impact" class="nav-link">Impact</a></li>
                    <li class="nav-item user-menu">
                        <div class="user-dropdown">
                            <a href="#" class="dropdown-item">
                                <i class="fa-solid fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fa-solid fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Analytics</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item logout-item" onclick="handleLogout()">
                                <i class="fa-solid fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </li>
                </ul>
            @else
                {{-- Guest Navigation --}}
                <ul class="nav-menu">
                    <li class="nav-item"><a href="{{ route('home') }}#home" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#roles" class="nav-link">Roles</a></li>
                    <li class="nav-item"><a href="{{ route('home') }}#impact" class="nav-link">Impact</a></li>
                    <li class="nav-item"><a href="{{ route('auth') }}" class="nav-cta" aria-label="Sign in">Sign In</a></li>
                </ul>
            @endauth
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
</body>
</html>
