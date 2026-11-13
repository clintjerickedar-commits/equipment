@extends('layouts.auth-split')
@section('title', 'Log in')
@section('side_icon', 'person')
@section('role_label', 'BORROWER ACCESS')
@section('side_text')Borrow equipment.<br>Get more done.@endsection
@section('side_chips')
    <span class="chip"><i class="bi bi-search"></i> Live availability</span>
    <span class="chip"><i class="bi bi-patch-check"></i> Fast approvals</span>
    <span class="chip"><i class="bi bi-alarm"></i> Return reminders</span>
@endsection
@section('foot_note', 'Secure sign-in to your account')

@section('content')
    <div class="card-head">
        <span class="glow-circle"><i class="bi bi-person"></i></span>
        <h1>Welcome back!</h1>
        <p>Log in to manage your borrowing requests.</p>
    </div>

    @if($errors->has('email') && !$errors->has('password'))
        <div class="alert alert-danger d-flex gap-2" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><div>{{ $errors->first('email') }}</div></div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <div class="input-icon"><i class="bi bi-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@school.edu" autocomplete="email" required autofocus></div>
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
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        @include('partials.recaptcha')
        <button class="btn btn-primary btn-lg btn-cta w-100"><i class="bi bi-box-arrow-in-right"></i> Log in <i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-divider">New to EquipTrack?</div>
    <a href="{{ route('register') }}" class="btn btn-ghost-red w-100"><i class="bi bi-person-plus"></i> Create an account</a>

    <div class="text-center mt-4"><a href="{{ route('admin.login') }}" class="auth-link-admin"><i class="bi bi-shield-lock"></i> Administrator sign in</a></div>
@endsection
