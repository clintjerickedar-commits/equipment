@extends('layouts.app')
@section('title', 'Requests')
@section('content')
<x-page-header title="Borrowing requests" subtitle="Review, approve or reject requests." />
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Request no. or borrower"></div>
            <div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>
                @foreach(\App\Models\BorrowingRequest::STATUSES as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Borrow date from"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Borrow date to"></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.requests.index') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Request</th><th>Borrower</th><th>Items</th><th>Borrow date</th><th>Return by</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
            @forelse($requests as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->request_no }}</td>
                    <td>{{ $r->user->name }}</td>
                    <td>@foreach($r->items as $i)<div>{{ $i->display_name }} <span class="text-muted">×{{ $i->quantity }}</span></div>@endforeach</td>
                    <td>{{ $r->borrow_date->format('M j, Y') }}</td>
                    <td>{{ $r->expected_return_date->format('M j, Y') }}</td>
                    <td><x-status-badge :status="$r->display_status" /></td>
                    <td class="text-end"><a href="{{ route('admin.requests.show', $r) }}" class="btn btn-sm {{ $r->status === 'pending' ? 'btn-primary' : 'btn-light' }}">{{ $r->status === 'pending' ? 'Review' : 'View' }}</a></td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="inbox" message="No requests match your filters." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())<div class="p-3 border-top">{{ $requests->links() }}</div>@endif
</div>
@endsection
