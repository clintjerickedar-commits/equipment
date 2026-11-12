@extends('layouts.app')
@section('title', 'Borrowers')
@section('content')
<x-page-header title="Borrowers" subtitle="Registered borrower accounts." />
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name, email or ID"></div>
            <div class="col-md-3"><select name="status" class="form-select"><option value="">All accounts</option>
                <option value="active" @selected(request('status')=='active')>Active</option><option value="inactive" @selected(request('status')=='inactive')>Deactivated</option></select></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.borrowers.index') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Name</th><th>ID number</th><th>Email</th><th>Requests</th><th>Account</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($borrowers as $b)
                <tr>
                    <td><a href="{{ route('admin.borrowers.show', $b) }}" class="fw-semibold">{{ $b->name }}</a></td>
                    <td>{{ $b->id_number }}</td><td>{{ $b->email }}</td><td>{{ $b->borrowing_requests_count }}</td>
                    <td><x-status-badge :status="$b->is_active ? 'active' : 'inactive'" /></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.borrowers.show', $b) }}" class="btn btn-sm btn-icon btn-light" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.borrowers.edit', $b) }}" class="btn btn-sm btn-icon btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.borrowers.toggle', $b) }}" class="d-inline"
                              data-confirm="{{ $b->is_active ? 'Deactivate' : 'Activate' }} the account of {{ $b->name }}?" data-confirm-button="{{ $b->is_active ? 'Deactivate' : 'Activate' }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-icon btn-light" title="{{ $b->is_active ? 'Deactivate' : 'Activate' }}"><i class="bi {{ $b->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="people" message="No borrowers found." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($borrowers->hasPages())<div class="p-3 border-top">{{ $borrowers->links() }}</div>@endif
</div>
@endsection
