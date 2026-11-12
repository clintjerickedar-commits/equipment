@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
    <div class="otp-icon"><i class="bi bi-shield-lock"></i></div>
    <h1 class="h3 mb-1">Choose a new password</h1>
    <p class="text-muted mb-4">Use at least 8 characters with letters and numbers.</p>
    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <div class="input-icon"><i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                <button type="button" class="toggle-pw no-loading" data-toggle-password="#password" aria-label="Show or hide password"><i class="bi bi-eye"></i></button></div>
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>
        <button class="btn btn-primary btn-lg w-100">Update password</button>
    </form>
@endsection
