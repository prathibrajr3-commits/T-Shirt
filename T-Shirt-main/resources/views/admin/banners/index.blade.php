@extends('layouts.admin')

@section('title', 'Manage Banners')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Banners</h1>
    <a href="{{ route('admin.banners.create') }}" class="btn btn-premium"><i class="fa-solid fa-plus me-2"></i> Add Banner</a>
</div>

<div class="glass-panel p-4">
    <h4 class="brand-font mb-4">Homepage Sliders</h4>

    @if($banners->isEmpty())
        <div class="text-center py-5 text-secondary">
            <i class="fa-solid fa-images fs-1 mb-3 opacity-50"></i>
            <p>No banners created yet.</p>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-premium btn-sm mt-2">Add First Banner</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 150px;">Image</th>
                        <th>Title</th>
                        <th>Subtitle</th>
                        <th>Button Text</th>
                        <th>Button URL</th>
                        <th class="text-center">Order Position</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banners as $banner)
                        <tr>
                            <td>
                                <img src="{{ asset($banner->image_path) }}" class="rounded border border-secondary border-opacity-25" style="width: 120px; height: 60px; object-fit: cover;" alt="">
                            </td>
                            <td class="fw-bold text-white">{{ $banner->title }}</td>
                            <td>{{ $banner->subtitle ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $banner->button_text }}</span></td>
                            <td><code>{{ $banner->button_link }}</code></td>
                            <td class="text-center fw-bold">{{ $banner->order_position }}</td>
                            <td class="text-center">
                                @if($banner->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-premium-outline btn-sm py-1">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0 align-middle" onclick="return confirm('Delete this banner slide?')">
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
    @endif
</div>
@endsection
