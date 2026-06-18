@extends('layouts.app')

@section('title', 'Shop T-Shirts')

@section('content')
<div class="row g-4">
    <!-- Filters Sidebar -->
    <div class="col-md-3">
        <div class="glass-panel p-4 sticky-md-top" style="top: 100px; z-index: 10;">
            <h4 class="brand-font mb-4">Filters</h4>
            
            <form action="{{ route('shop.list') }}" method="GET">
                <!-- Search -->
                <div class="mb-4">
                    <label class="form-label form-label-custom">Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-custom" placeholder="Search shirts..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-premium px-3"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </div>

                <!-- Categories -->
                <div class="mb-4">
                    <label class="form-label form-label-custom">Category</label>
                    <div class="category-filter-scroll-container">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('shop.list', ['search' => request('search'), 'sort' => request('sort')]) }}" 
                               class="text-decoration-none py-1 {{ !request('category') ? 'text-primary fw-bold' : 'text-secondary' }}">
                                All Categories
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('shop.list', ['category' => $cat->slug, 'search' => request('search'), 'sort' => request('sort')]) }}" 
                                   class="text-decoration-none py-1 {{ request('category') === $cat->slug ? 'text-primary fw-bold' : 'text-secondary' }}">
                                    {{ $cat->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                </div>

                <!-- Sort -->
                <div class="mb-4">
                    <label class="form-label form-label-custom">Sort By</label>
                    <select name="sort" class="form-select form-control-custom" onchange="this.form.submit()">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>

                <a href="{{ route('shop.list') }}" class="btn btn-premium-outline w-100">Clear All Filters</a>
            </form>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="brand-font">
                @if(request('category'))
                    {{ $categories->firstWhere('slug', request('category'))->name }} Collection
                @else
                    All Collections
                @endif
                <span class="text-secondary small fs-6">({{ $products->total() }} results)</span>
            </h3>
        </div>

        @if($products->isEmpty())
            <div class="glass-panel p-5 text-center">
                <i class="fa-solid fa-shirt-slash fs-1 text-secondary mb-3"></i>
                <h4 class="brand-font">No products found</h4>
                <p class="text-secondary">Try adjusting your filters or search terms.</p>
                <a href="{{ route('shop.list') }}" class="btn btn-premium mt-3">Reset Filters</a>
            </div>
        @else
            <div class="row g-4 mb-4">
                @foreach($products as $product)
                    <div class="col-md-4 col-sm-6">
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
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
