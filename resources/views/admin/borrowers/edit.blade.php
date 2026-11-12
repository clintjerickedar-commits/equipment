@extends('layouts.app')
@section('title', 'Edit borrower')
@section('content')
<x-page-header title="Edit borrower" :subtitle="$borrower->email"><a href="{{ route('admin.borrowers.show', $borrower) }}" class="btn btn-light">Cancel</a></x-page-header>
<form method="POST" action="{{ route('admin.borrowers.update', $borrower) }}" class="card" novalidate>
    @csrf @method('PUT')
    <div class="card-body">
        <div class="row">
            @foreach(['name' => 'Full name', 'email' => 'Email', 'id_number' => 'ID number', 'phone' => 'Phone'] as $field => $label)
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $borrower->$field) }}" class="form-control @error($field) is-invalid @enderror">
                    @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endforeach
        </div>
        <button class="btn btn-primary">Save changes</button>
    </div>
</form>
@endsection
