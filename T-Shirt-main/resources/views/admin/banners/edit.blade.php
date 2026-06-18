@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Edit Banner</h1>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-premium-outline"><i class="fa-solid fa-arrow-left me-2"></i> Back to Banners</a>
</div>

<div class="glass-panel p-4 p-md-5">
    @if ($errors->any())
        <div class="alert alert-custom-danger mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="title" class="form-label form-label-custom">Banner Title</label>
                <input type="text" name="title" id="title" class="form-control form-control-custom" value="{{ old('title', $banner->title) }}" required placeholder="e.g. New Oversized T-Shirts Collection">
            </div>
            <div class="col-md-6 mb-3">
                <label for="subtitle" class="form-label form-label-custom">Banner Subtitle</label>
                <input type="text" name="subtitle" id="subtitle" class="form-control form-control-custom" value="{{ old('subtitle', $banner->subtitle) }}" placeholder="e.g. Trendy Styles Starting From ₹499">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="button_text" class="form-label form-label-custom">Button CTA Text</label>
                <input type="text" name="button_text" id="button_text" class="form-control form-control-custom" value="{{ old('button_text', $banner->button_text) }}" required placeholder="e.g. Shop Now">
            </div>
            <div class="col-md-6 mb-3">
                <label for="button_link" class="form-label form-label-custom">Button Redirect URL / Slug</label>
                <input type="text" name="button_link" id="button_link" class="form-control form-control-custom" value="{{ old('button_link', $banner->button_link) }}" required placeholder="e.g. /shop?category=oversized">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label form-label-custom">Current Banner Background</label>
                <div class="mb-3">
                    <img src="{{ asset($banner->image_path) }}" class="rounded border border-secondary border-opacity-25" style="width: 100%; max-height: 150px; object-fit: cover;" alt="">
                </div>
                <label for="image" class="form-label form-label-custom">Upload New Background Image (Optional)</label>
                <input type="file" name="image" id="image" class="form-control form-control-custom" accept="image/*">
                <span class="text-secondary small">Leave blank to keep current background. Max 2MB.</span>
            </div>
            <div class="col-md-6 mb-3">
                <label for="order_position" class="form-label form-label-custom">Sort / Order Position</label>
                <input type="number" name="order_position" id="order_position" class="form-control form-control-custom" value="{{ old('order_position', $banner->order_position) }}" required min="0">
            </div>
        </div>

        <div class="mb-4 mt-2">
            <div class="form-check form-switch p-3 rounded bg-dark bg-opacity-25 border border-secondary border-opacity-25">
                <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" id="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}>
                <label class="form-check-label text-white fw-bold" for="is_active">
                    Active Slide (Display on home slider)
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-premium px-5 py-3">Save Banner Changes</button>
    </form>
</div>
@endsection
