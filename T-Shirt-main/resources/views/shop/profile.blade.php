@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="row g-4">
    <!-- Navigation Panel -->
    <div class="col-md-3">
        <div class="glass-panel p-4">
            <h4 class="brand-font mb-4">Account</h4>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('profile.index') }}" class="text-decoration-none py-2 text-primary fw-bold">
                    <i class="fa-regular fa-user me-2"></i> Profile Details
                </a>
                <a href="{{ route('orders.index') }}" class="text-decoration-none py-2 text-secondary">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i> Order History
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Profile Details Form -->
    <div class="col-md-9">
        <div class="glass-panel p-4 p-md-5">
            <h3 class="brand-font mb-4">Profile Details</h3>

            @if ($errors->any())
                <div class="alert alert-custom-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label form-label-custom">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-custom" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label form-label-custom">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control form-control-custom" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label form-label-custom">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control form-control-custom" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 000-0000">
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label form-label-custom">Default Shipping Address</label>
                    <textarea name="address" id="address" class="form-control form-control-custom" rows="3" placeholder="Street Address, City, Zip, Country">{{ old('address', $user->address) }}</textarea>
                </div>

                <hr class="border-secondary opacity-25 my-4">

                <h4 class="brand-font fs-5 mb-3 text-primary"><i class="fa-solid fa-key me-2"></i> Change Password (Leave blank to keep current)</h4>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label form-label-custom">New Password</label>
                        <input type="password" name="password" id="password" class="form-control form-control-custom" placeholder="••••••••">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label form-label-custom">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-custom" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn btn-premium px-4 py-2 mt-3">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
