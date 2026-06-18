@extends('layouts.admin')

@section('title', 'Manage Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Products</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-premium"><i class="fa-solid fa-plus me-2"></i> Add Product</a>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Product Inventory</h4>

    @if($products->isEmpty())
        <div class="text-center py-5 text-secondary">
            <i class="fa-solid fa-shirt fs-1 mb-3 opacity-50"></i>
            <p>Your product inventory is currently empty.</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn-premium btn-sm mt-2">Add First Product</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-end">Base Price</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Sizes</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <img src="{{ asset($product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="rounded" style="width: 45px; height: 45px; object-fit: cover;" alt="">
                            </td>
                            <td class="fw-bold text-white">{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                            <td class="text-end text-success fw-bold">
                                {{ $product->discount_price ? '₹' . number_format($product->discount_price, 2) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($product->stock === 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($product->stock < 10)
                                    <span class="badge bg-warning text-dark">{{ $product->stock }} left</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @foreach($product->sizes as $sz)
                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $sz }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-premium-outline btn-sm py-1">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0 align-middle" onclick="return confirm('Delete product permanently?')">
                                            <i class="fa-solid fa-trash-can fs-6"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
