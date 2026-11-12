@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
    <div class="auth-icon"><i class="bi bi-key"></i></div>
    <h1 class="h3 mb-1">Forgot your password?</h1>
    <p class="text-muted mb-4">Enter your account email and we'll send you a link to reset it.</p>
    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <div class="input-icon"><i class="bi bi-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus></div>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <button class="btn btn-primary btn-lg w-100">Send reset link</button>
    </form>
    <p class="text-center mt-4 mb-0"><a href="{{ route('login') }}" class="fw-semibold"><i class="bi bi-arrow-left"></i> Back to login</a></p>
@endsection
