@extends('layouts.app')
@section('title', $borrower->name)
@section('content')
<x-page-header :title="$borrower->name" subtitle="Borrower profile and history">
    <a href="{{ route('admin.borrowers.index') }}" class="btn btn-light">Back</a>
    <a href="{{ route('admin.borrowers.edit', $borrower) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
</x-page-header>
<div class="card mb-4"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $borrower->email }}</dd>
        <dt class="col-sm-3">ID number</dt><dd class="col-sm-9">{{ $borrower->id_number }}</dd>
        <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $borrower->phone ?: '—' }}</dd>
        <dt class="col-sm-3">Email verified</dt><dd class="col-sm-9">{{ $borrower->email_verified_at ? $borrower->email_verified_at->format('M j, Y') : 'Not yet' }}</dd>
        <dt class="col-sm-3">Account</dt><dd class="col-sm-9 mb-0"><x-status-badge :status="$borrower->is_active ? 'active' : 'inactive'" /></dd>
    </dl>
</div></div>
<div class="card">
    <div class="card-header">Borrowing history</div>
    @include('partials.history-table', ['history' => $history, 'showBorrower' => false])
</div>
@endsection
