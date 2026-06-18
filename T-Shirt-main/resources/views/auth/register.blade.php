@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-6">
        <div class="glass-panel p-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-user-plus fs-1 text-primary mb-3"></i>
                <h2 class="brand-font">Create Account</h2>
                <p class="text-secondary">Register to start purchasing our premium T-Shirts.</p>
            </div>

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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label form-label-custom">Full Name</label>
                        <input type="text" class="form-control form-control-custom" id="name" name="name" value="{{ old('name') }}" required placeholder="John Doe">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label form-label-custom">Email Address</label>
                        <input type="email" class="form-control form-control-custom" id="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label form-label-custom">Phone Number (Optional)</label>
                    <input type="text" class="form-control form-control-custom" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label form-label-custom">Shipping Address (Optional)</label>
                    <textarea class="form-control form-control-custom" id="address" name="address" rows="2" placeholder="123 Street, City, Country">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="password" class="form-label form-label-custom">Password</label>
                        <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="password_confirmation" class="form-label form-label-custom">Confirm Password</label>
                        <input type="password" class="form-control form-control-custom" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3 mb-3">Sign Up</button>
            </form>

            <div class="text-center mt-4">
                <span class="text-secondary">Already have an account? </span>
                <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection
