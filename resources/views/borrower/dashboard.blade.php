@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="card welcome mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <p>{{ $stats['overdue'] > 0 ? 'You have '.$stats['overdue'].' overdue item(s). Please return them as soon as you can.' : 'Everything is on track. Need something new? Start a request in a minute.' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('borrower.requests.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New request</a>
            <a href="{{ route('borrower.equipment') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-camera-video me-1"></i> Browse equipment</a>
            <a href="{{ route('borrower.requests.index') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-inbox me-1"></i> My requests</a>
            <a href="{{ route('borrower.history') }}" class="btn btn-light-soft btn-sm"><i class="bi bi-clock-history me-1"></i> History</a>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3"><x-stat-card icon="hourglass-split" tone="amber" label="Pending requests" :value="$stats['pending']" /></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="box-arrow-up-right" tone="blue" label="Currently borrowed" :value="$stats['active']" /></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="alarm" tone="red" label="Overdue items" :value="$stats['overdue']" /></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="check2-all" tone="green" label="Returned items" :value="$stats['returned']" /></div>
</div>

<div class="row g-2">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Return deadlines</div>
            <div class="list-group list-group-flush">
                @forelse($deadlines as $r)
                    @php($days = (int) now()->startOfDay()->diffInDays($r->expected_return_date, false))
                    <a href="{{ route('borrower.requests.show', $r) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between gap-2">
                            <strong class="text-truncate">{{ $r->items->map(fn ($i) => $i->display_name.' ×'.$i->quantity)->implode(', ') }}</strong>
                            <span class="badge-soft {{ $days < 0 ? 'b-overdue' : ($days <= 2 ? 'b-pending' : 'b-approved') }}">
                                @if($days < 0) {{ abs($days) }}d late @elseif($days === 0) Due today @else {{ $days }}d left @endif
                            </span>
                        </div>
                        <small class="text-muted">Return by {{ $r->expected_return_date->format('M j, Y') }}</small>
                    </a>
                @empty
                    <x-empty-state icon="calendar-check" message="You have nothing to return." />
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Recent requests <a href="{{ route('borrower.requests.index') }}" class="small fw-semibold">View all</a></div>
            <div class="list-group list-group-flush">
                @forelse($recent as $r)
                    <a href="{{ route('borrower.requests.show', $r) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2">
                        <div class="min-w-0"><strong>{{ $r->request_no }}</strong><div class="small text-muted text-truncate">{{ $r->items->map(fn ($i) => $i->display_name)->implode(', ') }}</div></div>
                        <x-status-badge :status="$r->display_status" />
                    </a>
                @empty
                    <x-empty-state icon="inbox" message="You have not made any requests yet."><a href="{{ route('borrower.equipment') }}" class="btn btn-primary btn-sm mt-2">Browse equipment</a></x-empty-state>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Available to borrow now <a href="{{ route('borrower.equipment') }}" class="small fw-semibold">Browse all</a></div>
            <div class="list-group list-group-flush">
                @forelse($available as $e)
                    <div class="list-group-item d-flex align-items-center gap-2">
                        <x-equipment-thumb :item="$e" />
                        <div class="flex-grow-1 min-w-0"><div class="fw-semibold text-truncate">{{ $e->name }}</div><div class="small text-muted">{{ $e->category->name }} · {{ $e->available_quantity }} available</div></div>
                        <a href="{{ route('borrower.requests.create', ['equipment' => $e->id]) }}" class="btn btn-sm btn-primary">Borrow</a>
                    </div>
                @empty
                    <x-empty-state icon="box-seam" message="Nothing is available right now." />
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
