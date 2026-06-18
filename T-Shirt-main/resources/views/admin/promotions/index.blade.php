@extends('layouts.admin')

@section('title', 'Promotion Offers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 brand-font text-white">
            <i class="fa-solid fa-tags text-primary me-2"></i> Promotion Offers
        </h1>
        <p class="text-secondary small mb-0">Manage promotional banners displayed on the homepage.</p>
    </div>
    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary px-4">
        <i class="fa-solid fa-plus me-2"></i> New Promotion
    </a>
</div>

@if($promotions->isEmpty())
    <div class="glass-panel p-5 text-center">
        <i class="fa-solid fa-tag fa-3x text-secondary mb-3 d-block"></i>
        <h5 class="text-white mb-2">No promotion offers yet</h5>
        <p class="text-secondary mb-4">Create your first promotional offer to display on the homepage.</p>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary px-4">
            <i class="fa-solid fa-plus me-2"></i> Create First Promotion
        </a>
    </div>
@else
    <div class="row g-4">
        @foreach($promotions as $promo)
            @php
                $isExpired = $promo->is_expired;
                $isStarted = $promo->is_started;
                $c1 = $promo->gradient_color_1;
                $c2 = $promo->gradient_color_2;
            @endphp
            <div class="col-12">
                <div class="card bg-dark border-0 shadow-lg overflow-hidden position-relative"
                     style="border-left: 3px solid {{ $promo->is_active && !$isExpired ? $c1 : '#555' }} !important; border-radius: 12px;">
                    
                    {{-- Status ribbon --}}
                    @if($isExpired)
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-danger">EXPIRED</span>
                        </div>
                    @elseif(!$promo->is_active)
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-secondary">INACTIVE</span>
                        </div>
                    @elseif(!$isStarted)
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-warning text-dark">SCHEDULED</span>
                        </div>
                    @else
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success">LIVE</span>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            {{-- Gradient Preview --}}
                            <div class="col-md-1 col-2">
                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                     style="width: 56px; height: 56px; background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }});">
                                    <i class="fa-solid fa-tag text-white fs-5"></i>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="col-md-6 col-10">
                                <p class="text-secondary small mb-0 text-uppercase" style="letter-spacing: 1px;">{{ $promo->title }}</p>
                                <h5 class="text-white mb-1 fw-bold" style="font-size: 1rem;">{{ $promo->heading }}</h5>
                                @if($promo->coupon_code)
                                    <span class="badge me-1" style="background: rgba(139,92,246,0.2); color: #a78bfa; border: 1px solid rgba(139,92,246,0.4); font-size: 0.8rem; letter-spacing: 1px;">
                                        <i class="fa-solid fa-scissors me-1"></i> {{ $promo->coupon_code }}
                                    </span>
                                @endif
                                <span class="badge bg-dark text-secondary border border-secondary me-1" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ $promo->button_link }}
                                </span>
                            </div>

                            {{-- Dates --}}
                            <div class="col-md-2 d-none d-md-block">
                                @if($promo->start_date || $promo->expiry_date)
                                    <div class="text-secondary small">
                                        @if($promo->start_date)
                                            <div><i class="fa-regular fa-calendar me-1 text-info"></i> {{ $promo->start_date->format('d M Y') }}</div>
                                        @endif
                                        @if($promo->expiry_date)
                                            <div><i class="fa-regular fa-calendar-xmark me-1 {{ $isExpired ? 'text-danger' : 'text-warning' }}"></i>
                                                {{ $promo->expiry_date->format('d M Y') }}
                                                @if($isExpired) <span class="text-danger">(Expired)</span> @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-secondary small"><i class="fa-solid fa-infinity me-1"></i> No expiry</span>
                                @endif
                            </div>

                            {{-- Order --}}
                            <div class="col-md-1 d-none d-md-block text-center">
                                <span class="badge bg-secondary bg-opacity-25 text-secondary" style="font-size: 0.8rem;">#{{ $promo->display_order }}</span>
                            </div>

                            {{-- Actions --}}
                            <div class="col-md-2 col-12">
                                <div class="d-flex gap-2 flex-wrap justify-content-start justify-content-md-end">
                                    {{-- Toggle active --}}
                                    <form action="{{ route('admin.promotions.toggle', $promo->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $promo->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $promo->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fa-solid {{ $promo->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Preview --}}
                                    <a href="{{ route('admin.promotions.preview', $promo->id) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info"
                                       title="Preview">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.promotions.edit', $promo->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.promotions.destroy', $promo->id) }}" method="POST"
                                          onsubmit="return confirm('Delete this promotion offer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
