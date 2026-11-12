@extends('layouts.app')
@section('title', 'Profile & settings')
@section('content')
@php($initials = collect(explode(' ', trim($user->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode(''))
<x-page-header title="Profile & settings" subtitle="Manage your personal information and password." />

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-center"><div class="card-body py-4">
            <span class="avatar lg mx-auto mb-3">{{ $initials }}</span>
            <h5 class="mb-0">{{ $user->name }}</h5>
            <div class="text-muted mb-3">{{ $user->email }}</div>
            <span class="badge-soft {{ $user->isAdmin() ? 'b-returned' : 'b-active' }}">{{ $user->isAdmin() ? 'Administrator' : 'Borrower' }}</span>
            <hr>
            <div class="text-start small">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Member since</span><strong>{{ $user->created_at->format('M j, Y') }}</strong></div>
            </div>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Account information</div>
            <form method="POST" action="{{ route('profile.update') }}" class="card-body" novalidate>
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label" for="name">Full name</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="email">Email</label>
                        <input id="email" value="{{ $user->email }}" class="form-control" readonly>
                        <div class="form-text">Your email is your login and can't be changed here.</div></div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="id_number">ID number</label>
                        <input id="id_number" name="id_number" value="{{ old('id_number', $user->id_number) }}" class="form-control @error('id_number') is-invalid @enderror">
                        @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                </div>
                <button class="btn btn-primary">Save changes</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">Change password</div>
            <form method="POST" action="{{ route('profile.password') }}" class="card-body" novalidate>
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label" for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label" for="password">New password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        <div class="form-text">At least 8 characters with letters and numbers.</div>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    <div class="col-md-6 mb-3"><label class="form-label" for="password_confirmation">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required></div>
                </div>
                <button class="btn btn-navy">Update password</button>
            </form>
        </div>
    </div>
</div>
@endsection
