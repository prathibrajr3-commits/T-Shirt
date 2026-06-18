@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="glass-panel p-4 p-md-5 mb-5">
    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-md-6">
            <div class="rounded-4 overflow-hidden position-relative" style="background: #1e293b; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <img src="{{ asset($product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="img-fluid w-100" style="object-fit: cover; max-height: 500px;" alt="{{ $product->name }}">
                @if($product->discount_price)
                    <span class="position-absolute top-3 start-3 badge bg-danger text-uppercase badge-custom fs-6 px-3 py-2">Sale</span>
                @endif
            </div>
        </div>

        <!-- Product Purchase Information -->
        <div class="col-md-6">
            <span class="badge badge-custom badge-primary-custom mb-3">{{ $product->category->name }}</span>
            <h1 class="brand-font display-5 mb-2">{{ $product->name }}</h1>
            
            <!-- Rating stars -->
            <div class="d-flex align-items-center mb-3">
                <div class="text-warning me-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($avgRating))
                            <i class="fa-solid fa-star"></i>
                        @else
                            <i class="fa-regular fa-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-secondary">({{ count($product->reviews) }} reviews)</span>
            </div>

            <!-- Price -->
            <div class="mb-4">
                @if($product->discount_price)
                    <span class="fs-4 text-decoration-line-through text-secondary me-3">₹{{ number_format($product->price, 2) }}</span>
                    <span class="fs-2 text-success fw-bold">₹{{ number_format($product->discount_price, 2) }}</span>
                @else
                    <span class="fs-2 text-white fw-bold">₹{{ number_format($product->price, 2) }}</span>
                @endif
            </div>

            <p class="text-secondary mb-4" style="line-height: 1.7; font-size: 1.05rem;">
                {{ $product->description }}
            </p>

            <hr class="border-secondary opacity-25 my-4">

            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                    @csrf
                    <!-- Sizes Select -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Select Size</label>
                        <div class="d-flex gap-2">
                            @foreach($product->sizes as $size)
                                <input type="radio" class="btn-check" name="size" id="size-{{ $size }}" value="{{ $size }}" required {{ $loop->first ? 'checked' : '' }}>
                                <label class="btn btn-premium-outline py-2 px-3" for="size-{{ $size }}">{{ $size }}</label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Colors Select -->
                    <div class="mb-4">
                        <label class="form-label form-label-custom">Select Color</label>
                        <div class="d-flex gap-2">
                            @foreach($product->colors as $color)
                                <input type="radio" class="btn-check" name="color" id="color-{{ $color }}" value="{{ $color }}" required {{ $loop->first ? 'checked' : '' }}>
                                <label class="btn btn-premium-outline py-2 px-3" for="color-{{ $color }}">{{ $color }}</label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Quantity and Add to Cart -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-4 col-md-3">
                            <label class="form-label form-label-custom">Quantity</label>
                            <input type="number" name="quantity" class="form-control form-control-custom text-center" value="1" min="1" max="{{ $product->stock }}" required>
                        </div>
                        <div class="col-8 col-md-9">
                            <button type="submit" class="btn btn-premium w-100 py-3">
                                <i class="fa-solid fa-cart-shopping me-2"></i> Add to Bag
                            </button>
                        </div>
                    </div>

                    <div class="text-secondary small mb-0">
                        <i class="fa-solid fa-check text-success me-2"></i> In Stock ({{ $product->stock }} units available)
                    </div>
                </form>
            @else
                <div class="alert alert-custom-danger py-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Out of Stock. This product is currently unavailable.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="glass-panel p-4 p-md-5 h-100">
            <h3 class="brand-font mb-4">Customer Reviews</h3>

            @if($product->reviews->isEmpty())
                <div class="text-center py-5 text-secondary">
                    <i class="fa-regular fa-comment-dots fs-1 mb-3 opacity-50"></i>
                    <p>No reviews yet for this product. Be the first to review!</p>
                </div>
            @else
                <div class="pe-md-2" style="max-height: 400px; overflow-y: auto;">
                    @foreach($product->reviews as $review)
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-white font-weight-bold">{{ $review->user->name }}</h6>
                                <span class="text-secondary small">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-warning small mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                @endfor
                            </div>
                            <p class="text-secondary mb-0 small" style="line-height: 1.5;">
                                {{ $review->comment }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Review Form -->
    <div class="col-md-6">
        <div class="glass-panel p-4 p-md-5 h-100">
            <h3 class="brand-font mb-4">Write a Review</h3>

            @auth
                <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label form-label-custom">Rating</label>
                        <select name="rating" class="form-select form-control-custom" required>
                            <option value="5">5 Stars (Excellent)</option>
                            <option value="4">4 Stars (Good)</option>
                            <option value="3">3 Stars (Average)</option>
                            <option value="2">2 Stars (Poor)</option>
                            <option value="1">1 Star (Terrible)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label form-label-custom">Comment</label>
                        <textarea name="comment" class="form-control form-control-custom" rows="4" placeholder="Share your experience with this t-shirt..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-premium px-4 py-2">Submit Review</button>
                </form>
            @else
                <div class="text-center py-5 text-secondary">
                    <p>Please log in to submit a review.</p>
                    <a href="{{ route('login') }}" class="btn btn-premium-outline">Login</a>
                </div>
            @endauth
        </div>
    </div>
</div>

<!-- Related Products -->
@if(!$relatedProducts->isEmpty())
    <div class="mb-5">
        <h3 class="brand-font mb-4">You May Also Like</h3>
        <div class="row g-4">
            @foreach($relatedProducts as $related)
                <div class="col-md-3 col-sm-6">
                    <div class="glass-card h-100 d-flex flex-column">
                        <a href="{{ route('shop.show', $related->slug) }}">
                            <div class="product-image-container">
                                <img src="{{ asset($related->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="product-image" alt="{{ $related->name }}">
                            </div>
                        </a>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <span class="text-secondary small mb-1">{{ $related->category->name }}</span>
                            <a href="{{ route('shop.show', $related->slug) }}" class="text-decoration-none">
                                <h5 class="text-white fs-6 mb-2 text-truncate" title="{{ $related->name }}">{{ $related->name }}</h5>
                            </a>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    @if($related->discount_price)
                                        <span class="price-discounted">₹{{ number_format($related->price, 2) }}</span>
                                        <span class="price-active">₹{{ number_format($related->discount_price, 2) }}</span>
                                    @else
                                        <span class="price-text">₹{{ number_format($related->price, 2) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('shop.show', $related->slug) }}" class="btn btn-premium-outline btn-sm py-1 px-2">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
