@extends('layouts.auth-split')
@section('title', 'Administrator sign in')
@section('side_icon', 'shield-lock')
@section('role_label', 'ADMINISTRATOR ACCESS')
@section('side_text', 'Restricted area. Authorized staff only.')
@section('side_chips')
    <span class="chip"><i class="bi bi-person-lock"></i> Role-based access</span>
    <span class="chip"><i class="bi bi-speedometer"></i> Rate-limited sign-in</span>
    <span class="chip"><i class="bi bi-shield-check"></i> Protected admin panel</span>
@endsection
@section('foot_note', 'Protected area · sign-in attempts are rate-limited')

@section('content')
    <div class="card-head">
        <span class="glow-circle"><i class="bi bi-shield-lock"></i></span>
        <h1>Administrator sign in</h1>
        <p>Access the admin panel securely.</p>
    </div>

    @if($errors->has('email'))
        <div class="alert alert-danger d-flex gap-2" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><div>{{ $errors->first('email') }}</div></div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Admin email</label>
            <div class="input-icon"><i class="bi bi-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="admin@school.edu" autocomplete="username" required autofocus></div>
        </div>
        <div class="mb-3">
            <div class="label-row"><label for="password" class="form-label">Password</label><a href="{{ route('password.request') }}">Forgot password?</a></div>
            <div class="input-icon"><i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" autocomplete="current-password" required>
                <button type="button" class="toggle-pw no-loading" data-toggle-password="#password" aria-label="Show or hide password"><i class="bi bi-eye"></i></button></div>
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Keep me signed in</label>
        </div>
        @include('partials.recaptcha')
        <button class="btn btn-primary btn-lg btn-cta w-100"><i class="bi bi-shield-check"></i> Sign in to admin panel <i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-divider">Not an administrator?</div>
    <a href="{{ route('home') }}" class="btn btn-ghost-red w-100"><i class="bi bi-arrow-left"></i> Back to website</a>
    <div class="text-center mt-3"><a href="{{ route('login') }}" class="auth-link-admin"><i class="bi bi-person"></i> Borrower login</a></div>
@endsection
