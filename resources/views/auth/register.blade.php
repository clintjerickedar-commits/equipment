@extends('layouts.guest')
@section('title', 'Create account')
@section('card_class', 'wide')
@section('foot_note', 'Secure sign-up with instant account access')

@section('content')
    <div class="card-head">
        <span class="glow-circle"><i class="bi bi-person-plus"></i></span>
        <h1>Create your account</h1>
        <p>Join EquipTrack and start borrowing equipment today.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full name</label>
                <div class="input-icon"><i class="bi bi-person"></i>
                    <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name" autocomplete="name" required autofocus></div>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-icon"><i class="bi bi-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@gmail.com" autocomplete="email" required></div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="id_number" class="form-label">Student / Employee ID</label>
                <div class="input-icon"><i class="bi bi-person-vcard"></i>
                    <input id="id_number" name="id_number" value="{{ old('id_number') }}" class="form-control @error('id_number') is-invalid @enderror" placeholder="2024-0001" required></div>
                @error('id_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone number</label>
                <div class="input-icon"><i class="bi bi-telephone"></i>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="09XX XXX XXXX" autocomplete="tel"></div>
                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-icon"><i class="bi bi-lock"></i>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="At least 8 characters" autocomplete="new-password" required>
                    <button type="button" class="toggle-pw no-loading" data-toggle-password="#password" aria-label="Show or hide password"><i class="bi bi-eye"></i></button></div>
                <div class="form-text">Use letters and numbers.</div>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <div class="input-icon"><i class="bi bi-shield-lock"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat your password" autocomplete="new-password" required>
                    <button type="button" class="toggle-pw no-loading" data-toggle-password="#password_confirmation" aria-label="Show or hide password"><i class="bi bi-eye"></i></button></div>
            </div>
        </div>
        @include('partials.recaptcha')
        <button class="btn btn-primary btn-lg btn-cta w-100"><i class="bi bi-person-check"></i> Create account <i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-divider">Already have an account?</div>
    <a href="{{ route('login') }}" class="btn btn-ghost-red w-100"><i class="bi bi-box-arrow-in-right"></i> Log in instead</a>
@endsection
