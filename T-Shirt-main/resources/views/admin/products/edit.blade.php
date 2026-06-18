@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Edit Product</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Inventory</a>
</div>

<div class="glass-panel p-4 p-md-5">
    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-custom-danger mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label form-label-custom">Product Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-custom" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label form-label-custom">Category</label>
                <select name="category_id" id="category_id" class="form-select form-control-custom" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label form-label-custom">Description</label>
            <textarea name="description" id="description" class="form-control form-control-custom" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="price" class="form-label form-label-custom">Price (₹)</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control form-control-custom" value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="discount_price" class="form-label form-label-custom">Discount Price (₹) (Optional)</label>
                <input type="number" step="0.01" name="discount_price" id="discount_price" class="form-control form-control-custom" value="{{ old('discount_price', $product->discount_price) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="stock" class="form-label form-label-custom">Inventory Stock</label>
                <input type="number" name="stock" id="stock" class="form-control form-control-custom" value="{{ old('stock', $product->stock) }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="sizes" class="form-label form-label-custom">Available Sizes</label>
                <input type="text" name="sizes" id="sizes" class="form-control form-control-custom" value="{{ old('sizes', $sizesString) }}" required>
                <span class="text-secondary small">Comma separated values (e.g. S, M, L, XL)</span>
            </div>
            <div class="col-md-6 mb-4">
                <label for="colors" class="form-label form-label-custom">Available Colors</label>
                <input type="text" name="colors" id="colors" class="form-control form-control-custom" value="{{ old('colors', $colorsString) }}" required>
                <span class="text-secondary small">Comma separated values (e.g. Black, White, Navy)</span>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-center mb-3 mb-md-0">
                <span class="text-secondary small d-block mb-2">Current Showcase Image</span>
                <img src="{{ asset($product->image_path ?? 'images/tshirts/cyberpunk.png') }}" class="rounded img-fluid" style="max-height: 120px; object-fit: cover;" alt="">
            </div>
            <div class="col-md-9">
                <label for="image" class="form-label form-label-custom">Replace Product Image (Optional)</label>
                <input type="file" name="image" id="image" class="form-control form-control-custom">
                <span class="text-secondary small">Max size: 2MB. Valid extensions: jpg, png, jpeg, webp. Leave empty to keep current image.</span>
            </div>
        </div>

        <button type="submit" class="btn btn-premium w-100 py-3 mt-3">Update Product Details</button>
    </form>
</div>
@endsection
