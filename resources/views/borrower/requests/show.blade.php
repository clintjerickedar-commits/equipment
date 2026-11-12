@extends('layouts.app')
@section('title', $req->request_no)
@section('content')
<x-page-header :title="'Request '.$req->request_no" :subtitle="'Submitted '.$req->created_at->format('M j, Y g:i A')">
    <a href="{{ route('borrower.requests.index') }}" class="btn btn-light">Back</a>
    @if($req->status === 'pending')
        <form method="POST" action="{{ route('borrower.requests.cancel', $req) }}" data-confirm="Cancel this request?" data-confirm-title="Cancel request" data-confirm-button="Cancel request">
            @csrf @method('PATCH')<button class="btn btn-outline-danger">Cancel request</button>
        </form>
    @endif
</x-page-header>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Equipment</div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Equipment</th><th>Quantity</th></tr></thead>
                <tbody>@foreach($req->items as $i)<tr><td><div class="d-flex align-items-center gap-3"><x-equipment-thumb :item="$i->equipment" />{{ $i->display_name }}</div></td><td>{{ $i->quantity }}</td></tr>@endforeach</tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <div class="mb-3"><x-status-badge :status="$req->display_status" /></div>
            <dl class="mb-0">
                <dt>Borrow date</dt><dd>{{ $req->borrow_date->format('M j, Y') }}</dd>
                <dt>Return by</dt><dd class="{{ $req->is_late ? 'text-danger fw-semibold' : '' }}">{{ $req->expected_return_date->format('M j, Y') }}</dd>
                @if($req->actual_return_date)<dt>Returned on</dt><dd>{{ $req->actual_return_date->format('M j, Y') }}</dd>@endif
                <dt>Purpose</dt><dd>{{ $req->purpose }}</dd>
                @if($req->rejection_reason)<dt>Rejection reason</dt><dd class="mb-0 text-danger">{{ $req->rejection_reason }}</dd>@endif
            </dl>
        </div></div>
    </div>
</div>
@endsection
