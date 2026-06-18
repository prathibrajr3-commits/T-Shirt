@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="glass-panel p-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-shirt fs-1 text-primary mb-3"></i>
                <h2 class="brand-font">Welcome Back</h2>
                <p class="text-secondary">Login to access your profile and track orders.</p>
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

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label form-label-custom">Email Address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label form-label-custom">Password</label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="••••••••">
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input bg-dark border-secondary" id="remember" name="remember">
                    <label class="form-check-label text-secondary small" for="remember">Remember me on this device</label>
                </div>

                <button type="submit" class="btn btn-premium w-100 py-3 mb-3">Sign In</button>
            </form>

            <div class="text-center mt-4">
                <span class="text-secondary">Don't have an account? </span>
                <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-bold">Sign Up</a>
            </div>
            
            <div class="text-center mt-3 p-3 bg-dark bg-opacity-25 rounded border border-secondary border-opacity-25">
                <p class="mb-1 text-secondary small">Demo Access Credentials:</p>
                <code class="text-info d-block small">Admin: admin@tshirt.com / admin123</code>
                <code class="text-info d-block small">User: customer@example.com / customer123</code>
            </div>
        </div>
    </div>
</div>
@endsection
