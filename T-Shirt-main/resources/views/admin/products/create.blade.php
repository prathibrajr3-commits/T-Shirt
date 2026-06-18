@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Add Product</h1>
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

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label form-label-custom">Product Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-custom" value="{{ old('name') }}" required placeholder="e.g. Vintage Oversized Tee">
            </div>

            <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label form-label-custom">Category</label>
                <select name="category_id" id="category_id" class="form-select form-control-custom" required>
                    <option value="" disabled selected>Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label form-label-custom">Description</label>
            <textarea name="description" id="description" class="form-control form-control-custom" rows="4" placeholder="Detailed details about design, cotton quality, thickness etc.">{{ old('description') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="price" class="form-label form-label-custom">Price (₹)</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control form-control-custom" value="{{ old('price') }}" required placeholder="799.00">
            </div>
            <div class="col-md-4 mb-3">
                <label for="discount_price" class="form-label form-label-custom">Discount Price (₹) (Optional)</label>
                <input type="number" step="0.01" name="discount_price" id="discount_price" class="form-control form-control-custom" value="{{ old('discount_price') }}" placeholder="599.00">
            </div>
            <div class="col-md-4 mb-3">
                <label for="stock" class="form-label form-label-custom">Inventory Stock</label>
                <input type="number" name="stock" id="stock" class="form-control form-control-custom" value="{{ old('stock', 0) }}" required placeholder="20">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="sizes" class="form-label form-label-custom">Available Sizes</label>
                <input type="text" name="sizes" id="sizes" class="form-control form-control-custom" value="{{ old('sizes', 'S,M,L,XL') }}" required placeholder="S, M, L, XL">
                <span class="text-secondary small">Comma separated values (e.g. S, M, L, XL)</span>
            </div>
            <div class="col-md-6 mb-4">
                <label for="colors" class="form-label form-label-custom">Available Colors</label>
                <input type="text" name="colors" id="colors" class="form-control form-control-custom" value="{{ old('colors', 'Black,White,Navy') }}" required placeholder="Black, White, Navy">
                <span class="text-secondary small">Comma separated values (e.g. Black, White, Navy)</span>
            </div>
        </div>

        <div class="mb-4">
            <label for="image" class="form-label form-label-custom">Product Showcase Image</label>
            <input type="file" name="image" id="image" class="form-control form-control-custom">
            <span class="text-secondary small">Max size: 2MB. Valid extensions: jpg, png, jpeg, webp. Leave empty to use fallback cyberpunk image.</span>
        </div>

        <button type="submit" class="btn btn-premium w-100 py-3 mt-3">Add Product to Inventory</button>
    </form>
</div>
@endsection
