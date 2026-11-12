@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<x-page-header title="Settings" subtitle="Borrowing rules applied to every new request." />
<div class="row g-2">
    <div class="col-xl-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="card h-100" novalidate>
            @csrf @method('PUT')
            <div class="card-header">Borrowing limits</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="max_borrow_days">Maximum borrowing period (days)</label>
                        <input type="number" min="0" max="365" id="max_borrow_days" name="max_borrow_days" value="{{ old('max_borrow_days', $maxDays) }}" class="form-control @error('max_borrow_days') is-invalid @enderror" required>
                        <div class="form-text">Return date can be at most this many days after the borrow date. 0 = no limit.</div>
                        @error('max_borrow_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="max_quantity_per_item">Maximum quantity per item, per request</label>
                        <input type="number" min="0" max="1000" id="max_quantity_per_item" name="max_quantity_per_item" value="{{ old('max_quantity_per_item', $maxQty) }}" class="form-control @error('max_quantity_per_item') is-invalid @enderror" required>
                        <div class="form-text">A borrower cannot request more than this many units of one item at once. 0 = no limit.</div>
                        @error('max_quantity_per_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <button class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">Current rules</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Borrowing period</span><strong>{{ $maxDays > 0 ? $maxDays.' day(s) max' : 'No limit' }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Quantity per item</span><strong>{{ $maxQty > 0 ? $maxQty.' unit(s) max' : 'No limit' }}</strong></li>
            </ul>
        </div>
    </div>
</div>
@endsection
