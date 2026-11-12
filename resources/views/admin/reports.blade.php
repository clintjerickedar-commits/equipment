@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<x-page-header title="Reports" subtitle="Filter, review and print.">
    <button class="btn btn-primary no-loading" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print report</button>
</x-page-header>

<div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Report</label>
            <select name="type" class="form-select">@foreach($types as $k => $v)<option value="{{ $k }}" @selected($type==$k)>{{ $v }}</option>@endforeach</select></div>
        @if($type === 'inventory')
            <div class="col-md-3"><label class="form-label">Category</label>
                <select name="category" class="form-select"><option value="">All</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Status</label>
                <select name="status" class="form-select"><option value="">All</option>@foreach(\App\Models\Equipment::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>@endforeach</select></div>
        @else
            <div class="col-md-2"><label class="form-label">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
            @if($type === 'transactions')
                <div class="col-md-2"><label class="form-label">Status</label>
                    <select name="status" class="form-select"><option value="">All</option>@foreach(\App\Models\BorrowingRequest::STATUSES as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            @endif
        @endif
        <div class="col-auto"><button class="btn btn-navy">Generate</button> <a href="{{ route('admin.reports') }}" class="btn btn-light">Reset</a></div>
    </form>
</div></div>

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <h2 class="h4 mb-0">{{ $types[$type] }}</h2>
            <div class="text-muted small">Equipment Borrowing Management System · Generated {{ now()->format('M j, Y g:i A') }} by {{ auth()->user()->name }}
                @if(request('from') || request('to')) · Period: {{ request('from') ?: 'start' }} to {{ request('to') ?: 'today' }} @endif
                · {{ $rows->count() }} record(s)</div>
        </div>
        <div class="table-responsive">
        @if($type === 'inventory')
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Total</th><th>Available</th><th>Borrowed</th><th>Condition</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($rows as $e)
                    <tr><td>{{ $e->equipment_code }}</td><td>{{ $e->name }}</td><td>{{ $e->category->name }}</td><td>{{ $e->quantity }}</td><td>{{ $e->available_quantity }}</td><td>{{ $e->borrowed_quantity }}</td>
                        <td>{{ \App\Models\Equipment::CONDITIONS[$e->condition] ?? $e->condition }}</td><td><x-status-badge :status="$e->status" /></td></tr>
                @empty<tr><td colspan="8"><x-empty-state icon="file-earmark" message="No records for this report." /></td></tr>@endforelse
                </tbody>
                @if($rows->count())<tfoot><tr class="fw-semibold"><td colspan="3">Totals</td><td>{{ $rows->sum('quantity') }}</td><td>{{ $rows->sum('available_quantity') }}</td><td>{{ $rows->sum('borrowed_quantity') }}</td><td colspan="2"></td></tr></tfoot>@endif
            </table>
        @else
            <table class="table">
                <thead><tr><th>Request</th><th>Borrower</th><th>Equipment</th><th>Qty</th><th>Borrowed</th><th>Expected return</th><th>Actual return</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $r->request_no }}</td><td>{{ $r->user->name }}</td>
                        <td>@foreach($r->items as $i)<div>{{ $i->display_name }}</div>@endforeach</td>
                        <td>@foreach($r->items as $i)<div>{{ $i->quantity }}</div>@endforeach</td>
                        <td>{{ $r->borrow_date->format('M j, Y') }}</td><td>{{ $r->expected_return_date->format('M j, Y') }}</td>
                        <td>{{ $r->actual_return_date?->format('M j, Y') ?? '—' }}</td><td><x-status-badge :status="$r->display_status" /></td></tr>
                @empty<tr><td colspan="8"><x-empty-state icon="file-earmark" message="No records for this report." /></td></tr>@endforelse
                </tbody>
            </table>
        @endif
        </div>
    </div>
</div>
@endsection
