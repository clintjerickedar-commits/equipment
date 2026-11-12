@extends('layouts.app')
@section('title', 'My requests')
@section('content')
<x-page-header title="My requests" subtitle="Track the status of everything you have asked for.">
    <a href="{{ route('borrower.requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New request</a>
</x-page-header>
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><select name="status" class="form-select"><option value="">All statuses</option>
                @foreach(\App\Models\BorrowingRequest::STATUSES as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-navy">Filter</button><a href="{{ route('borrower.requests.index') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Request</th><th>Items</th><th>Borrow date</th><th>Return by</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($requests as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->request_no }}</td>
                    <td>@foreach($r->items as $i)<div>{{ $i->display_name }} <span class="text-muted">×{{ $i->quantity }}</span></div>@endforeach</td>
                    <td>{{ $r->borrow_date->format('M j, Y') }}</td>
                    <td>{{ $r->expected_return_date->format('M j, Y') }}</td>
                    <td><x-status-badge :status="$r->display_status" /></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('borrower.requests.show', $r) }}" class="btn btn-sm btn-light">View</a>
                        @if($r->status === 'pending')
                            <form method="POST" action="{{ route('borrower.requests.cancel', $r) }}" class="d-inline"
                                  data-confirm="Cancel request {{ $r->request_no }}?" data-confirm-title="Cancel request" data-confirm-button="Cancel request">
                                @csrf @method('PATCH')<button class="btn btn-sm btn-light text-danger">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="inbox" message="You have no requests yet."><a href="{{ route('borrower.requests.create') }}" class="btn btn-primary btn-sm mt-3">Create your first request</a></x-empty-state></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())<div class="p-3 border-top">{{ $requests->links() }}</div>@endif
</div>
@endsection
