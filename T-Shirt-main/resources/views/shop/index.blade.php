@extends('layouts.app')

@section('title', 'Premium Apparel')

@section('content')
<!-- Hero Slider Section -->
@if($banners->isEmpty())
    <!-- Fallback Static Hero Section -->
    <div class="glass-panel p-5 mb-5 d-flex align-items-center position-relative overflow-hidden" style="min-height: 450px;">
        <div class="row align-items-center w-100 z-3">
            <div class="col-md-7 text-start ps-md-4">
                <span class="badge badge-custom badge-primary-custom mb-3">Summer Collection 2026</span>
                <h1 class="display-3 font-weight-bold mb-3 brand-font">STYLE THAT <br><span class="text-primary">SPEAKS VOLUME</span></h1>
                <p class="text-secondary fs-5 mb-4 max-width-500">
                    Explore our curated collection of luxury heavy-weight streetwear, retro prints, minimal graphics, and premium crop tees.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('shop.list') }}" class="btn btn-premium btn-lg px-4 py-3">Shop Collection <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    <a href="{{ route('shop.list', ['category' => 'oversized']) }}" class="btn btn-premium-outline btn-lg px-4 py-3">Explore Oversized</a>
                </div>
            </div>
            <div class="col-md-5 d-none d-md-block text-center position-relative">
                <div style="width: 350px; height: 350px; background: linear-gradient(135deg, rgba(139,92,246,0.3) 0%, rgba(59,130,246,0.3) 100%); border-radius: 50%; filter: blur(50px); position: absolute; top: 0; left: 10%; z-index: 1;"></div>
                <img src="{{ asset('images/tshirts/cyberpunk.png') }}" class="img-fluid rounded-4 position-relative" style="max-height: 350px; z-index: 2; box-shadow: 0 20px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15);" alt="Hero Apparel">
            </div>
        </div>
    </div>
@else
    <!-- Dynamic Slider -->
    <div class="hero-slider-container mb-5 position-relative overflow-hidden rounded-4">
        <div class="hero-slider">
            @foreach($banners as $index => $banner)
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ asset($banner->image_path) }}');">
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-content container d-flex flex-column justify-content-center h-100 text-start z-3 position-relative ps-4 ps-md-5">
                        <div class="max-width-600">
                            <span class="badge badge-custom badge-primary-custom mb-3 animate-fade-in-down">Exclusive Collection</span>
                            <h1 class="display-4 font-weight-bold mb-3 brand-font text-white animate-fade-in-left">{{ $banner->title }}</h1>
                            @if($banner->subtitle)
                                <p class="text-secondary fs-5 mb-4 animate-fade-in-left-delay">{{ $banner->subtitle }}</p>
                            @endif
                            <div class="animate-fade-in-up">
                                <a href="{{ url($banner->button_link) }}" class="btn btn-premium btn-lg px-4 py-3">{{ $banner->button_text }} <i class="fa-solid fa-chevron-right ms-2 small"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Left/Right Navigation Arrows -->
        <button class="slider-arrow prev-arrow" aria-label="Previous Slide">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="slider-arrow next-arrow" aria-label="Next Slide">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <!-- Bottom Indicator Dots -->
        <div class="slider-dots">
            @foreach($banners as $index => $banner)
                <span class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></span>
            @endforeach
        </div>
    </div>
@endif

<!-- Category Grid -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="brand-font">Browse Categories</h2>
    </div>
    <div class="row g-4">
        @foreach($categories as $category)
            <div class="col-6 col-md-3">
                <a href="{{ route('shop.list', ['category' => $category->slug]) }}" class="text-decoration-none">
                    <div class="glass-card p-4 text-center h-100 d-flex flex-column justify-content-center align-items-center">
                        <div class="mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-glow); border: 1px solid rgba(139,92,246,0.2);">
                            @if($category->slug === 'men')
                                <i class="fa-solid fa-mars fs-4 text-primary"></i>
                            @elseif($category->slug === 'women')
                                <i class="fa-solid fa-venus fs-4 text-primary"></i>
                            @elseif($category->slug === 'oversized')
                                <i class="fa-solid fa-arrows-up-down-left-right fs-4 text-primary"></i>
                            @else
                                <i class="fa-solid fa-palette fs-4 text-primary"></i>
                            @endif
                        </div>
                        <h5 class="mb-1 brand-font">{{ $category->name }}</h5>
                        <p class="text-secondary small mb-0">{{ Str::limit($category->description, 50) }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<!-- Promo Banner — Dynamic (Admin-Controlled) -->
@if($activePromotion)
    @php
        $c1 = $activePromotion->gradient_color_1;
        $c2 = $activePromotion->gradient_color_2;
    @endphp
    <div class="promo-offer-section mb-5 position-relative overflow-hidden text-center"
         style="background: linear-gradient(135deg, {{ $c1 }}18 0%, {{ $c2 }}18 100%); border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 60px 40px;">

        {{-- Background image layer --}}
        @if($activePromotion->background_image)
            <div class="position-absolute top-0 start-0 w-100 h-100"
                 style="background-image: url('{{ asset($activePromotion->background_image) }}'); background-size: cover; background-position: center; opacity: 0.12; z-index: 0; border-radius: 20px;"></div>
        @endif

        {{-- Glow orbs --}}
        <div class="position-absolute" style="width: 350px; height: 350px; top: -80px; left: -60px; background: {{ $c1 }}; border-radius: 50%; filter: blur(100px); opacity: 0.2; z-index: 1;"></div>
        <div class="position-absolute" style="width: 300px; height: 300px; bottom: -60px; right: -40px; background: {{ $c2 }}; border-radius: 50%; filter: blur(90px); opacity: 0.2; z-index: 1;"></div>

        {{-- Content --}}
        <div class="position-relative" style="z-index: 2;">
            <p class="text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 3px; color: #94a3b8; font-weight: 700;">
                {{ $activePromotion->title }}
            </p>

            <h2 class="brand-font mb-3"
                style="font-size: clamp(1.6rem, 4vw, 2.4rem); font-weight: 800; line-height: 1.15; background: linear-gradient(135deg, #fff 0%, #c4b5fd 60%, #93c5fd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ $activePromotion->heading }}
            </h2>

            @if($activePromotion->subtitle)
                <p class="text-secondary max-width-600 mx-auto mb-4" style="font-size: 1rem; line-height: 1.7;">
                    {{ $activePromotion->subtitle }}
                </p>
            @endif

            @if($activePromotion->coupon_code)
                <div class="d-flex justify-content-center mb-4">
                    <div class="promo-coupon-pill"
                         id="promoCoupon"
                         onclick="copyPromoCode('{{ $activePromotion->coupon_code }}')"
                         title="Click to copy code"
                         style="display: inline-flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.04); border: 1.5px dashed rgba(139,92,246,0.5); border-radius: 12px; padding: 10px 24px; cursor: pointer; transition: all 0.25s; position: relative;">
                        <div>
                            <div style="font-size: 0.65rem; letter-spacing: 1px; color: #64748b; text-transform: uppercase; font-weight: 700;">Coupon Code</div>
                            <div id="couponCodeText" style="font-size: 1.2rem; font-weight: 900; letter-spacing: 4px; background: linear-gradient(90deg, #a78bfa, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ $activePromotion->coupon_code }}</div>
                        </div>
                        <span id="promoCopyHint" style="font-size: 0.72rem; color: #64748b; font-weight: 600; transition: opacity 0.3s;">
                            <i class="fa-regular fa-copy"></i> Click to copy
                        </span>
                        <span id="promoCopied" style="font-size: 0.8rem; color: #a78bfa; font-weight: 700; opacity: 0; transition: opacity 0.3s; position: absolute; right: 20px;">
                            <i class="fa-solid fa-check"></i> Copied!
                        </span>
                    </div>
                </div>
            @endif

            <a href="{{ url($activePromotion->button_link) }}"
               class="btn btn-premium px-5 py-3 promo-cta-btn"
               style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }}); border: none; color: white; font-weight: 700; letter-spacing: 0.5px; border-radius: 50px; box-shadow: 0 6px 30px {{ $c1 }}44;">
                {{ $activePromotion->button_text }} <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
@endif

<!-- Featured Products -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="brand-font">New Arrivals</h2>
        <a href="{{ route('shop.list') }}" class="btn btn-premium-outline py-2 px-3">View All <i class="fa-solid fa-chevron-right ms-2 small"></i></a>
    </div>
    <div class="row g-4">
        @foreach($featuredProducts as $product)
            <div class="col-md-3 col-sm-6">
                <div class="glass-card h-100 d-flex flex-column">
                    <a href="{{ route('shop.show', $product->slug) }}">
                        <div class="product-image-container">
                            <img src="{{ asset($product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="product-image" alt="{{ $product->name }}">
                            @if($product->discount_price)
                                <span class="position-absolute top-3 start-3 badge bg-danger text-uppercase badge-custom" style="z-index: 10;">Sale</span>
                            @endif
                        </div>
                    </a>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <span class="text-secondary small mb-1">{{ $product->category->name }}</span>
                        <a href="{{ route('shop.show', $product->slug) }}" class="text-decoration-none">
                            <h5 class="text-white fs-6 mb-2 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h5>
                        </a>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                @if($product->discount_price)
                                    <span class="price-discounted">₹{{ number_format($product->price, 2) }}</span>
                                    <span class="price-active">₹{{ number_format($product->discount_price, 2) }}</span>
                                @else
                                    <span class="price-text">₹{{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('shop.show', $product->slug) }}" class="btn btn-premium-outline btn-sm py-1 px-2">
                                <i class="fa-solid fa-cart-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.querySelector('.hero-slider');
        if (!slider) return;

        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const prevArrow = document.querySelector('.prev-arrow');
        const nextArrow = document.querySelector('.next-arrow');
        
        let currentSlide = 0;
        const totalSlides = slides.length;
        let slideInterval;

        function showSlide(index) {
            if (index >= totalSlides) {
                currentSlide = 0;
            } else if (index < 0) {
                currentSlide = totalSlides - 1;
            } else {
                currentSlide = index;
            }

            // Move the slider container
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;

            // Update active states
            slides.forEach((slide, i) => {
                if (i === currentSlide) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            dots.forEach((dot, i) => {
                if (i === currentSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        // Start Auto Slide
        function startAutoSlide() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 3000); // 3 seconds
        }

        // Event Listeners for arrows
        if (nextArrow) {
            nextArrow.addEventListener('click', function() {
                nextSlide();
                startAutoSlide(); // reset timer on manual interaction
            });
        }

        if (prevArrow) {
            prevArrow.addEventListener('click', function() {
                prevSlide();
                startAutoSlide(); // reset timer on manual interaction
            });
        }

        // Event Listeners for dots
        dots.forEach(dot => {
            dot.addEventListener('click', function() {
                const slideIndex = parseInt(this.getAttribute('data-slide'));
                showSlide(slideIndex);
                startAutoSlide(); // reset timer on manual interaction
            });
        });

        // Initialize slider
        if (totalSlides > 0) {
            startAutoSlide();
        }
    });

    // ---- Promo Offer: Copy coupon code ----
    function copyPromoCode(code) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => showCopied());
        } else {
            // Fallback
            const el = document.createElement('textarea');
            el.value = code;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showCopied();
        }
    }
    function showCopied() {
        const hint   = document.getElementById('promoCopyHint');
        const copied = document.getElementById('promoCopied');
        const pill   = document.getElementById('promoCoupon');
        if (!hint || !copied) return;
        hint.style.opacity = '0';
        copied.style.opacity = '1';
        if (pill) {
            pill.style.borderColor = 'rgba(139,92,246,0.9)';
            pill.style.background  = 'rgba(139,92,246,0.1)';
        }
        setTimeout(() => {
            hint.style.opacity   = '1';
            copied.style.opacity = '0';
            if (pill) {
                pill.style.borderColor = 'rgba(139,92,246,0.5)';
                pill.style.background  = 'rgba(255,255,255,0.04)';
            }
        }, 2000);
    }

    // ---- Promo CTA button micro-animation ----
    document.querySelectorAll('.promo-cta-btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            btn.style.transform = 'translateY(-3px) scale(1.04)';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translateY(0) scale(1)';
        });
        btn.style.transition = 'transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s ease';
    });
</script>
@endsection
@endsection
