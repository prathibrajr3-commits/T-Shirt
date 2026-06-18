@extends('layouts.admin')

@section('title', 'Create Promotion Offer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 brand-font text-white">
            <i class="fa-solid fa-plus text-primary me-2"></i> Create Promotion Offer
        </h1>
        <p class="text-secondary small mb-0">Add a new promotional offer to display on your homepage.</p>
    </div>
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary px-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<form action="{{ route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data" id="promoForm">
    @csrf

    <div class="row g-4">
        {{-- Left Column: Main Content --}}
        <div class="col-lg-8">
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 mb-4">
                <div class="card-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 text-white"><i class="fa-solid fa-align-left text-primary me-2"></i> Offer Content</h6>
                </div>
                <div class="card-body p-4">
                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                            Section Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control bg-black border-secondary text-white @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', 'LIMITED TIME OFFER') }}"
                               placeholder="e.g. LIMITED TIME OFFER" maxlength="255">
                        <div class="form-text text-secondary">Displayed as a small label above the heading.</div>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Heading --}}
                    <div class="mb-3">
                        <label for="heading" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                            Main Heading <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control bg-black border-secondary text-white @error('heading') is-invalid @enderror"
                               id="heading" name="heading" value="{{ old('heading') }}"
                               placeholder="e.g. GET 15% OFF YOUR FIRST ORDER" maxlength="255">
                        @error('heading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Subtitle --}}
                    <div class="mb-3">
                        <label for="subtitle" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                            Subtitle / Description
                        </label>
                        <textarea class="form-control bg-black border-secondary text-white @error('subtitle') is-invalid @enderror"
                                  id="subtitle" name="subtitle" rows="3"
                                  placeholder="e.g. Use code AURA15 at checkout. Free delivery on orders over ₹2,500.">{{ old('subtitle') }}</textarea>
                        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Coupon Code --}}
                    <div class="mb-3">
                        <label for="coupon_code" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                            Coupon Code
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-black border-secondary text-secondary">
                                <i class="fa-solid fa-scissors"></i>
                            </span>
                            <input type="text" class="form-control bg-black border-secondary text-white @error('coupon_code') is-invalid @enderror"
                                   id="coupon_code" name="coupon_code" value="{{ old('coupon_code') }}"
                                   placeholder="e.g. AURA15" maxlength="50"
                                   style="text-transform: uppercase;"
                                   oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="form-text text-secondary">Leave blank to hide the coupon section.</div>
                        @error('coupon_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        {{-- Button Text --}}
                        <div class="col-md-6">
                            <label for="button_text" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                                Button Text <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-black border-secondary text-white @error('button_text') is-invalid @enderror"
                                   id="button_text" name="button_text" value="{{ old('button_text', 'Shop Now') }}"
                                   placeholder="e.g. Shop Now" maxlength="100">
                            @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Button Link --}}
                        <div class="col-md-6">
                            <label for="button_link" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                                Button Link <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-black border-secondary text-white @error('button_link') is-invalid @enderror"
                                   id="button_link" name="button_link" value="{{ old('button_link', '/shop') }}"
                                   placeholder="e.g. /shop">
                            @error('button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Background Image --}}
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 mb-4">
                <div class="card-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 text-white"><i class="fa-solid fa-image text-primary me-2"></i> Background Image <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">Optional</span></h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <input type="file" class="form-control bg-black border-secondary text-white @error('background_image') is-invalid @enderror"
                               id="background_image" name="background_image" accept="image/*"
                               onchange="previewImage(this)">
                        <div class="form-text text-secondary">Max 3MB. JPEG, PNG, WEBP. If not provided, the gradient is used as background.</div>
                        @error('background_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div id="imagePreviewBox" class="d-none rounded-3 overflow-hidden" style="max-height: 200px;">
                        <img id="imagePreview" src="" alt="Preview" class="img-fluid w-100" style="object-fit: cover; max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Settings --}}
        <div class="col-lg-4">
            {{-- Status & Order --}}
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 mb-4">
                <div class="card-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 text-white"><i class="fa-solid fa-sliders text-primary me-2"></i> Settings</h6>
                </div>
                <div class="card-body p-4">
                    {{-- Active toggle --}}
                    <div class="mb-4">
                        <div class="form-check form-switch d-flex align-items-center gap-3 p-0">
                            <input class="form-check-input flex-shrink-0" type="checkbox" role="switch"
                                   id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                                   style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            <div>
                                <label class="form-check-label text-white fw-semibold" for="is_active">Active</label>
                                <p class="text-secondary small mb-0">Show on homepage (if within date range).</p>
                            </div>
                        </div>
                    </div>

                    {{-- Display Order --}}
                    <div class="mb-3">
                        <label for="display_order" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                            Display Order <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control bg-black border-secondary text-white @error('display_order') is-invalid @enderror"
                               id="display_order" name="display_order" value="{{ old('display_order', 0) }}"
                               min="0" max="9999">
                        <div class="form-text text-secondary">Lower number = higher priority. If multiple active, the lowest order shows.</div>
                        @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Schedule --}}
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 mb-4">
                <div class="card-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 text-white"><i class="fa-regular fa-calendar text-primary me-2"></i> Schedule</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="start_date" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Start Date</label>
                        <input type="date" class="form-control bg-black border-secondary text-white @error('start_date') is-invalid @enderror"
                               id="start_date" name="start_date" value="{{ old('start_date') }}">
                        <div class="form-text text-secondary">Leave blank to start immediately.</div>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-1">
                        <label for="expiry_date" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Expiry Date</label>
                        <input type="date" class="form-control bg-black border-secondary text-white @error('expiry_date') is-invalid @enderror"
                               id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                        <div class="form-text text-secondary">Leave blank for no expiry.</div>
                        @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Gradient Colors --}}
            <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 mb-4">
                <div class="card-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 text-white"><i class="fa-solid fa-palette text-primary me-2"></i> Gradient Colors</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="gradient_color_1" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Color 1 (Start)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color bg-black border-secondary"
                                   id="gradient_color_1" name="gradient_color_1"
                                   value="{{ old('gradient_color_1', '#8b5cf6') }}"
                                   style="width: 60px; height: 44px;"
                                   oninput="updateGradientPreview()">
                            <input type="text" class="form-control bg-black border-secondary text-white" style="font-family: monospace;"
                                   id="gradient_color_1_text" value="{{ old('gradient_color_1', '#8b5cf6') }}"
                                   oninput="document.getElementById('gradient_color_1').value = this.value; updateGradientPreview()">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="gradient_color_2" class="form-label text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Color 2 (End)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color bg-black border-secondary"
                                   id="gradient_color_2" name="gradient_color_2"
                                   value="{{ old('gradient_color_2', '#3b82f6') }}"
                                   style="width: 60px; height: 44px;"
                                   oninput="updateGradientPreview()">
                            <input type="text" class="form-control bg-black border-secondary text-white" style="font-family: monospace;"
                                   id="gradient_color_2_text" value="{{ old('gradient_color_2', '#3b82f6') }}"
                                   oninput="document.getElementById('gradient_color_2').value = this.value; updateGradientPreview()">
                        </div>
                    </div>

                    {{-- Live gradient preview --}}
                    <div class="rounded-3 d-flex align-items-center justify-content-center" id="gradientPreview"
                         style="height: 60px; background: linear-gradient(135deg, #8b5cf622, #3b82f622); border: 1px solid rgba(255,255,255,0.08); transition: background 0.3s;">
                        <span class="text-white small" style="opacity: 0.7;">Gradient Preview</span>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold">
                <i class="fa-solid fa-check me-2"></i> Create Promotion Offer
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function previewImage(input) {
        const box = document.getElementById('imagePreviewBox');
        const img = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; box.classList.remove('d-none'); };
            reader.readAsDataURL(input.files[0]);
        } else {
            box.classList.add('d-none');
        }
    }

    function updateGradientPreview() {
        const c1 = document.getElementById('gradient_color_1').value;
        const c2 = document.getElementById('gradient_color_2').value;
        document.getElementById('gradient_color_1_text').value = c1;
        document.getElementById('gradient_color_2_text').value = c2;
        document.getElementById('gradientPreview').style.background =
            `linear-gradient(135deg, ${c1}33, ${c2}33)`;
    }
</script>
@endsection
