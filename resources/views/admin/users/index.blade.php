@extends('layouts.app')
@section('title', 'User accounts')
@section('content')
<x-page-header title="User accounts" subtitle="Administrators and borrowers who can sign in.">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add account</a>
</x-page-header>
<div class="card">
    <form method="GET" class="filter-bar row g-2">
        <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email or ID"></div>
        <div class="col-md-2"><select name="role" class="form-select"><option value="">All roles</option><option value="admin" @selected(request('role')=='admin')>Administrators</option><option value="borrower" @selected(request('role')=='borrower')>Borrowers</option></select></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">All accounts</option><option value="active" @selected(request('status')=='active')>Active</option><option value="inactive" @selected(request('status')=='inactive')>Deactivated</option></select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.users.index') }}" class="btn btn-light">Reset</a></div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>User</th><th>Role</th><th>ID number</th><th>Requests</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $u)
                @php($initials = collect(explode(' ', trim($u->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode(''))
                <tr>
                    <td><div class="d-flex align-items-center gap-3"><span class="avatar sm">{{ $initials }}</span>
                        <div><div class="fw-semibold">{{ $u->name }} @if($u->id === auth()->id())<span class="text-muted small">(you)</span>@endif</div><div class="small text-muted">{{ $u->email }}</div></div></div></td>
                    <td><span class="badge-soft {{ $u->isAdmin() ? 'b-returned' : 'b-cancelled' }}">{{ $u->isAdmin() ? 'Administrator' : 'Borrower' }}</span></td>
                    <td>{{ $u->id_number ?: '—' }}</td>
                    <td>{{ $u->borrowing_requests_count }}</td>
                    <td><x-status-badge :status="$u->is_active ? 'active' : 'inactive'" /></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-icon btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="d-inline"
                                  data-confirm="{{ $u->is_active ? 'Deactivate' : 'Activate' }} the account of {{ $u->name }}?" data-confirm-button="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                                @csrf @method('PATCH')<button class="btn btn-sm btn-icon btn-light" title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}"><i class="bi {{ $u->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline"
                                  data-confirm="Delete {{ $u->name }}? This cannot be undone." data-confirm-title="Delete account" data-confirm-button="Delete">
                                @csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="people" message="No accounts match your filters." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="p-3 border-top">{{ $users->links() }}</div>@endif
</div>
@endsection
