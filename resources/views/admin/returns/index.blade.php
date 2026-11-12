@extends('layouts.app')
@section('title', 'Returns')
@section('content')
<x-page-header title="Returns" subtitle="Approved borrowings that are still out." />
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Request no. or borrower"></div>
            <div class="col-md-3"><select name="filter" class="form-select"><option value="">All outstanding</option><option value="overdue" @selected(request('filter')=='overdue')>Overdue only</option></select></div>
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.returns.index') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Request</th><th>Borrower</th><th>Items</th><th>Due date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
            @forelse($transactions as $t)
                @php($late = $t->due_date->lt(now()->startOfDay()))
                <tr>
                    <td class="fw-semibold">{{ $t->request->request_no }}</td>
                    <td>{{ $t->request->user->name }}</td>
                    <td>@foreach($t->request->items as $i)<div>{{ $i->display_name }} <span class="text-muted">×{{ $i->quantity }}</span></div>@endforeach</td>
                    <td class="{{ $late ? 'text-danger fw-semibold' : '' }}">{{ $t->due_date->format('M j, Y') }}@if($late)<div class="small">{{ $t->due_date->diffForHumans() }}</div>@endif</td>
                    <td><x-status-badge :status="$late ? 'overdue' : 'approved'" /></td>
                    <td class="text-end"><a href="{{ route('admin.returns.create', $t) }}" class="btn btn-sm btn-primary">Process return</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="check2-all" message="Nothing is waiting to be returned." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())<div class="p-3 border-top">{{ $transactions->links() }}</div>@endif
</div>
@endsection
