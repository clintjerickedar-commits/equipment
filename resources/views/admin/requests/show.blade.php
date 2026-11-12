@extends('layouts.app')
@section('title', $req->request_no)
@section('content')
<x-page-header :title="'Request '.$req->request_no" :subtitle="'Submitted '.$req->created_at->format('M j, Y g:i A')">
    <a href="{{ route('admin.requests.index') }}" class="btn btn-light">Back</a>
    @if($req->status === 'pending')
        <button class="btn btn-outline-danger no-loading" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-circle"></i> Reject</button>
        <form method="POST" action="{{ route('admin.requests.approve', $req) }}" data-confirm="Approve this request? Available stock will be reduced." data-confirm-title="Approve request" data-confirm-button="Approve">
            @csrf <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Approve</button>
        </form>
    @elseif(in_array($req->status, ['approved', 'overdue']) && $req->transaction)
        <a href="{{ route('admin.returns.create', $req->transaction) }}" class="btn btn-primary"><i class="bi bi-arrow-return-left"></i> Process return</a>
    @endif
</x-page-header>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Equipment requested</div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Equipment</th><th>Requested</th><th>Available now</th></tr></thead>
                <tbody>@foreach($req->items as $i)
                    <tr><td><div class="d-flex align-items-center gap-3"><x-equipment-thumb :item="$i->equipment" /><div><div class="fw-semibold">{{ $i->display_name }}</div><div class="small text-muted">{{ $i->display_code }}</div></div></div></td>
                        <td>{{ $i->quantity }}</td>
                        <td class="{{ $req->status === 'pending' && $i->equipment && $i->quantity > $i->equipment->available_quantity ? 'text-danger fw-semibold' : '' }}">{{ $i->equipment?->available_quantity ?? '—' }}</td></tr>
                @endforeach</tbody>
            </table></div>
        </div>

        @if($req->transaction?->returnRecord)
            @php($ret = $req->transaction->returnRecord)
            <div class="card">
                <div class="card-header">Return record · {{ $ret->actual_return_date->format('M j, Y') }}</div>
                <div class="table-responsive"><table class="table">
                    <thead><tr><th>Equipment</th><th>Borrowed</th><th>Returned good</th><th>Damaged</th><th>Missing</th><th>Condition</th></tr></thead>
                    <tbody>@foreach($ret->items as $ri)
                        <tr><td>{{ $ri->display_name }}</td><td>{{ $ri->quantity_borrowed }}</td><td>{{ $ri->quantity_returned_good }}</td><td>{{ $ri->quantity_damaged }}</td><td>{{ $ri->quantity_missing }}</td><td>{{ ucfirst($ri->condition_on_return) }}</td></tr>
                    @endforeach</tbody>
                </table></div>
                @if($ret->notes)<div class="card-body border-top"><strong>Notes:</strong> {{ $ret->notes }}</div>@endif
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <div class="mb-3"><x-status-badge :status="$req->display_status" /></div>
            <dl class="mb-0">
                <dt>Borrower</dt><dd><a href="{{ route('admin.borrowers.show', $req->user) }}">{{ $req->user->name }}</a><div class="small text-muted">{{ $req->user->email }}</div></dd>
                <dt>Borrow date</dt><dd>{{ $req->borrow_date->format('M j, Y') }}</dd>
                <dt>Expected return</dt><dd>{{ $req->expected_return_date->format('M j, Y') }}</dd>
                <dt>Purpose</dt><dd>{{ $req->purpose }}</dd>
                @if($req->reviewer)<dt>Reviewed by</dt><dd>{{ $req->reviewer->name }} · {{ $req->reviewed_at?->format('M j, Y') }}</dd>@endif
                @if($req->rejection_reason)<dt>Rejection reason</dt><dd class="mb-0 text-danger">{{ $req->rejection_reason }}</dd>@endif
            </dl>
        </div></div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('admin.requests.reject', $req) }}" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Reject request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label" for="reason">Reason (the borrower will see this)</label>
            <textarea id="reason" name="rejection_reason" rows="3" class="form-control @error('rejection_reason') is-invalid @enderror" required>{{ old('rejection_reason') }}</textarea>
            @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light no-loading" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Reject request</button></div>
    </form></div>
</div>
@if($errors->has('rejection_reason'))
    @push('scripts')<script>new bootstrap.Modal(document.getElementById('rejectModal')).show();</script>@endpush
@endif
@endsection
