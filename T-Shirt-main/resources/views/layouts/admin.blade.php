<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ $favicon_url }}">
    <title>@yield('title', 'Admin Panel') - AuraWear Control Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar-admin collapse px-3" id="sidebarMenu">

            {{-- ① PINNED LOGO HEADER --}}
            <div class="text-center py-4 pb-3 border-bottom border-secondary border-opacity-25 flex-shrink-0">
                <a class="text-decoration-none d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-screwdriver-wrench me-2 text-primary fs-4"></i>
                    @if(isset($siteSetting) && $siteSetting->show_store_logo && $siteSetting->store_logo)
                        <img src="{{ asset('storage/' . $siteSetting->store_logo) }}" alt="{{ $siteSetting->store_name ?? 'AURAWEAR' }}" height="32" class="me-2" style="max-width: 100px; object-fit: contain;">
                    @endif
                    @if(isset($siteSetting) && $siteSetting->show_store_name)
                        <span class="logo-text brand-font text-white fs-4 fw-extrabold" style="letter-spacing: 1px; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ strtoupper($siteSetting->admin_header_title ?? 'AURA ADMIN') }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- ② SCROLLABLE NAV LINKS --}}
            <div class="sidebar-scrollable px-1 pt-3">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ Request::is('admin') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ Request::is('admin/categories*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i> Categories
                </a>
                <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ Request::is('admin/products*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shirt"></i> Products
                </a>
                <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ Request::is('admin/orders*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-packing"></i> Orders
                </a>
                <a href="{{ route('admin.returns.index') }}" class="sidebar-link {{ Request::is('admin/returns*') ? 'active' : '' }}">
                    <i class="fa-solid fa-rotate-left text-warning"></i> Returns & Refunds
                </a>
                <a href="{{ route('admin.banners.index') }}" class="sidebar-link {{ Request::is('admin/banners*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i> Banners
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="sidebar-link {{ Request::is('admin/promotions*') ? 'active' : '' }}">
                    <i class="fa-solid fa-percent text-warning"></i> Promotions
                </a>

                <div class="mt-4 pt-2 border-top border-secondary border-opacity-10">
                    <div class="text-secondary small px-3 mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Marketing</div>
                    <a href="{{ route('admin.coupons.index') }}" class="sidebar-link {{ Request::is('admin/coupons*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket text-warning"></i> Coupons
                    </a>
                </div>

                <div class="mt-4 pt-2 border-top border-secondary border-opacity-10">
                    <div class="text-secondary small px-3 mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Appearance</div>

                    <a href="{{ route('admin.site-settings.index') }}" class="sidebar-link {{ Request::is('admin/site-settings*') ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders text-primary"></i> Site Settings
                    </a>
                    <a href="{{ url('/') }}" class="sidebar-link" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-store text-info"></i> View Shop
                    </a>
                </div>
            </div>

            {{-- ③ PINNED FOOTER ACTIONS --}}
            <div class="sidebar-footer px-1">
                <a href="{{ url('/') }}" class="sidebar-link border border-secondary border-opacity-25 bg-dark bg-opacity-25">
                    <i class="fa-solid fa-store text-info"></i> View Storefront
                </a>
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit" class="sidebar-link text-danger w-100 bg-transparent border-0 text-start">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                    </button>
                </form>
            </div>

        </nav>

        <!-- Main Content Wrapper -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4" style="margin-left: 25%; min-height: 100vh;">
            <!-- Mobile Toggle Header -->
            <header class="d-md-none d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary border-opacity-25">
                <span class="brand-font text-white">Aura Admin</span>
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </header>

            <!-- Alerts Notification Banner -->
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
        </main>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
