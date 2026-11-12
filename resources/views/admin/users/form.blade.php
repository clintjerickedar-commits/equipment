@extends('layouts.app')
@php($editing = $user->exists)
@section('title', $editing ? 'Edit account' : 'Add account')
@section('content')
<x-page-header :title="$editing ? 'Edit account' : 'Add account'" :subtitle="$editing ? $user->email : 'Accounts created here are verified automatically.'">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
</x-page-header>
<form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card" novalidate>
    @csrf @if($editing) @method('PUT') @endif
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-4 mb-3"><label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" @disabled($editing && $user->id === auth()->id())>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator</option>
                    <option value="borrower" @selected(old('role', $user->role) === 'borrower')>Borrower</option>
                </select>
                @if($editing && $user->id === auth()->id())<input type="hidden" name="role" value="{{ $user->role }}"><div class="form-text">You can't change your own role.</div>@endif
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-4 mb-3"><label class="form-label" for="id_number">ID number</label>
                <input id="id_number" name="id_number" value="{{ old('id_number', $user->id_number) }}" class="form-control @error('id_number') is-invalid @enderror">
                @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-4 mb-3"><label class="form-label" for="phone">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="password">{{ $editing ? 'New password (optional)' : 'Password' }}</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $editing ? '' : 'required' }}>
                <div class="form-text">{{ $editing ? 'Leave blank to keep the current password. ' : '' }}At least 8 characters with letters and numbers.</div>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="password_confirmation">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"></div>
        </div>
        <button class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create account' }}</button>
    </div>
</form>
@endsection
