@extends('layouts.admin')

@section('title', 'Manage Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="brand-font h2">Categories</h1>
</div>

<div class="row g-4">
    <!-- Categories List Table -->
    <div class="col-lg-8">
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Existing Categories</h4>
            
            @if($categories->isEmpty())
                <p class="text-secondary py-4 text-center">No categories found. Create one on the right.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th class="text-center">Products</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td class="fw-bold text-white">
                                        <!-- Inline update form or standard display -->
                                        <form id="update-form-{{ $category->id }}" action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" class="form-control form-control-custom py-1 mb-2" value="{{ $category->name }}" required>
                                            <textarea name="description" class="form-control form-control-custom py-1 rows-2">{{ $category->description }}</textarea>
                                            <button type="submit" class="btn btn-premium btn-sm py-1 mt-2">Save</button>
                                            <button type="button" class="btn btn-premium-outline btn-sm py-1 mt-2" onclick="toggleEdit({{ $category->id }}, false)">Cancel</button>
                                        </form>
                                        <span id="name-display-{{ $category->id }}">{{ $category->name }}</span>
                                    </td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>
                                        <span id="desc-display-{{ $category->id }}">{{ Str::limit($category->description, 50) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $category->products_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" id="edit-btn-{{ $category->id }}" class="btn btn-premium-outline btn-sm py-1" onclick="toggleEdit({{ $category->id }}, true)">
                                                Edit
                                            </button>
                                            
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0 border-0 align-middle" onclick="return confirm('Delete category?')" {{ $category->products_count > 0 ? 'disabled title=Contains_products' : '' }}>
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
    </div>

    <!-- Create Category Form Sidebar -->
    <div class="col-lg-4">
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Create Category</h4>
            
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label form-label-custom">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-custom" required placeholder="e.g. Oversized, Printed">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label form-label-custom">Description</label>
                    <textarea name="description" id="description" class="form-control form-control-custom" rows="4" placeholder="Brief details about category..."></textarea>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3">Create Category</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleEdit(id, showForm) {
        const form = document.getElementById(`update-form-${id}`);
        const nameDisplay = document.getElementById(`name-display-${id}`);
        const descDisplay = document.getElementById(`desc-display-${id}`);
        const editBtn = document.getElementById(`edit-btn-${id}`);
        
        if (showForm) {
            form.classList.remove('d-none');
            nameDisplay.classList.add('d-none');
            if (descDisplay) descDisplay.classList.add('d-none');
            editBtn.classList.add('d-none');
        } else {
            form.classList.add('d-none');
            nameDisplay.classList.remove('d-none');
            if (descDisplay) descDisplay.classList.remove('d-none');
            editBtn.classList.remove('d-none');
        }
    }
</script>
@endsection
