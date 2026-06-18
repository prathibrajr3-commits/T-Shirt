<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', isset($siteSetting) ? $siteSetting->store_name : 'AuraWear') - {{ isset($siteSetting) && $siteSetting->tagline ? $siteSetting->tagline : 'Premium T-Shirt E-Commerce' }}</title>
    
    <link rel="icon" type="image/x-icon" href="{{ $favicon_url }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body>

    <!-- Premium Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                @php
                    $showLogo = !isset($siteSetting) || $siteSetting->show_store_logo;
                    $showText = !isset($siteSetting) || $siteSetting->show_store_name;
                    $storeName = isset($siteSetting) ? strtoupper($siteSetting->store_name) : 'AURAWEAR';
                    $logoPath  = isset($siteSetting) ? $siteSetting->store_logo : null;
                @endphp

                @if($showLogo && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}"
                         alt="{{ $storeName }}"
                         style="height: 32px; max-width: 120px; object-fit: contain; margin-right: 0.5rem;">
                @elseif($showLogo)
                    <i class="fa-solid fa-shirt me-2 text-primary"></i>
                @endif

                @if($showText)
                    <span class="logo-text brand-font" style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $storeName }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ Request::is('shop*') ? 'active' : '' }}" href="{{ route('shop.list') }}">Shop</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <!-- Cart Indicator -->
                    <a href="{{ route('cart.index') }}" class="nav-link nav-link-custom me-4 position-relative">
                        <i class="fa-solid fa-bag-shopping fs-5"></i>
                        @if(session()->has('cart') && count(session('cart')) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ collect(session('cart'))->sum('quantity') }}
                            </span>
                        @endif
                    </a>

                    <!-- User Account / Auth Dropdown -->
                    @auth
                        <div class="dropdown">
                            <a class="btn btn-premium dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-user me-2"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary bg-dark" aria-labelledby="userDropdown">
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge me-2 text-primary"></i>Admin Panel</a></li>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                @endif
                                <li><a class="dropdown-item py-2" href="{{ route('profile.index') }}"><i class="fa-regular fa-id-card me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('orders.index') }}"><i class="fa-solid fa-truck-ramp-box me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger" style="background: none; border: none; width: 100%; text-align: left;">
                                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-premium-outline me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-premium">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="py-5">
        <div class="container">
            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-custom-success d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-custom-danger d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Premium Footer -->
    <footer class="py-5 mt-auto" style="background: rgba(9, 13, 22, 0.9); border-top: 1px solid var(--border-color);">
        <div class="container">
            <div class="row g-4">
                @if(!isset($siteSetting) || $siteSetting->show_footer_logo)
                    <div class="col-md-6 col-lg-4">
                        <h5 class="brand-font mb-3">
                            @if(isset($siteSetting) && $siteSetting->store_logo)
                                <img src="{{ asset('storage/' . $siteSetting->store_logo) }}" alt="{{ $siteSetting->store_name ?? 'AURAWEAR' }}" style="height: 35px; max-width: 150px; object-fit: contain;">
                            @else
                                <i class="fa-solid fa-shirt me-2 text-primary"></i>{{ isset($siteSetting) ? $siteSetting->store_name : 'AURAWEAR' }}
                            @endif
                        </h5>
                        @if(isset($siteSetting) && $siteSetting->footer_title)
                            <h6 class="text-white brand-font mb-2">{{ $siteSetting->footer_title }}</h6>
                        @endif
                        <p class="text-secondary" style="font-size: 0.95rem; line-height: 1.6;">
                            {{ isset($siteSetting) && $siteSetting->footer_description ? $siteSetting->footer_description : 'High-quality cyberpunk, minimalist, retro, and printed street style t-shirts. Designed for durability and styled for comfort.' }}
                        </p>
                    </div>
                @endif

                <div class="col-6 col-lg-3 offset-lg-1">
                    <h6 class="brand-font mb-3 text-uppercase" style="letter-spacing: 1px;">Links</h6>
                    <ul class="list-unstyled">
                        @if(isset($footerLinks) && $footerLinks->isNotEmpty())
                            @foreach($footerLinks as $link)
                                <li class="mb-2"><a href="{{ url($link->url) }}" class="text-secondary text-decoration-none hover-primary">{{ $link->text }}</a></li>
                            @endforeach
                        @else
                            <li class="mb-2"><a href="{{ url('/') }}" class="text-secondary text-decoration-none hover-primary">Home</a></li>
                            <li class="mb-2"><a href="{{ route('shop.list') }}" class="text-secondary text-decoration-none hover-primary">Shop</a></li>
                            <li class="mb-2"><a href="{{ route('cart.index') }}" class="text-secondary text-decoration-none hover-primary">Cart</a></li>
                        @endif
                    </ul>
                </div>

                @if(!isset($siteSetting) || $siteSetting->show_footer_contact)
                    <div class="col-6 col-md-5 col-lg-3">
                        <h6 class="brand-font mb-3 text-uppercase" style="letter-spacing: 1px;">Contact</h6>
                        <ul class="list-unstyled text-secondary" style="font-size: 0.95rem;">
                            @if(isset($siteSetting) && ($siteSetting->email || $siteSetting->phone || $siteSetting->address))
                                @if($siteSetting->email)
                                    <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-primary"></i>{{ $siteSetting->email }}</li>
                                  @endif
                                @if($siteSetting->phone)
                                    <li class="mb-2"><i class="fa-solid fa-phone me-2 text-primary"></i>{{ $siteSetting->phone }}</li>
                                @endif
                                @if($siteSetting->address)
                                    <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-primary"></i>{{ $siteSetting->address }}</li>
                                @endif
                            @else
                                <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-primary"></i>support@aurawear.com</li>
                                <li class="mb-2"><i class="fa-solid fa-phone me-2 text-primary"></i>+1 (555) 123-4567</li>
                                <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Fashion District, NY</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
            <hr class="my-4 border-secondary opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-secondary" style="font-size: 0.9rem;">
                        {{ isset($siteSetting) && $siteSetting->copyright_text ? $siteSetting->copyright_text : '© ' . date('Y') . ' AuraWear Inc. All rights reserved.' }}
                    </span>
                </div>
                
                @if(!isset($siteSetting) || $siteSetting->show_footer_social)
                    <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                        <div class="d-inline-flex gap-3">
                            @if(isset($siteSetting) && ($siteSetting->instagram_url || $siteSetting->facebook_url || $siteSetting->twitter_url || $siteSetting->youtube_url || $siteSetting->linkedin_url || $siteSetting->whatsapp_url))
                                @if($siteSetting->instagram_url)
                                    <a href="{{ $siteSetting->instagram_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-instagram"></i></a>
                                @endif
                                @if($siteSetting->facebook_url)
                                    <a href="{{ $siteSetting->facebook_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-facebook-f"></i></a>
                                @endif
                                @if($siteSetting->twitter_url)
                                    <a href="{{ $siteSetting->twitter_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-x-twitter"></i></a>
                                @endif
                                @if($siteSetting->youtube_url)
                                    <a href="{{ $siteSetting->youtube_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-youtube"></i></a>
                                @endif
                                @if($siteSetting->linkedin_url)
                                    <a href="{{ $siteSetting->linkedin_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-linkedin-in"></i></a>
                                @endif
                                @if($siteSetting->whatsapp_url)
                                    <a href="{{ $siteSetting->whatsapp_url }}" target="_blank" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-whatsapp"></i></a>
                                @endif
                            @else
                                <a href="#" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-instagram"></i></a>
                                <a href="#" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-twitter"></i></a>
                                <a href="#" class="text-secondary hover-primary fs-5"><i class="fa-brands fa-facebook-f"></i></a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
