<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ReCircle - Circular Economy Marketplace')</title>
    <meta name="description" content="@yield('meta_description', 'A marketplace where waste is transformed into products with measurable impact.')" />

    <!-- Tailwind for utility classes (via CDN to match template). You can remove if you compile Tailwind locally. -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/app.css','resources/css/style.css'])
    <script>
        window.appRoutes = {
            home: @json(route('home')),
            auth: @json(route('auth')),
            twofa: @json(route('twofa')),
            forgot: @json(route('forgot-password')),
            dashboard: @json(route('dashboard', [], false)),
        };
    </script>
    @vite(['resources/js/app.js','resources/js/main.js'])
    @stack('head')
</head>
<body>
    {{-- Navbar (shared) --}}
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2><a href="{{ route('home') }}" class="nav-link" style="text-decoration:none;">ReCircle</a></h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="{{ route('home') }}#home" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#how-it-works" class="nav-link">How It Works</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#roles" class="nav-link">Roles</a></li>
                <li class="nav-item"><a href="{{ route('home') }}#impact" class="nav-link">Impact</a></li>
                <li class="nav-item"><a href="{{ route('auth') }}" class="nav-cta" aria-label="Sign in">Sign In</a></li>
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

    @stack('scripts')
</body>
</html>
