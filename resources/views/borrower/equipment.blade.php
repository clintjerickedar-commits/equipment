@extends('layouts.app')
@section('title', 'Browse equipment')
@section('content')
<x-page-header title="Browse equipment" subtitle="Pick what you need, then send a request." />

<div class="card mb-4"><form method="GET" class="filter-bar row g-2">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search equipment"></div>
    <div class="col-md-3"><select name="category" class="form-select"><option value="">All categories</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="availability" class="form-select"><option value="">Any availability</option><option value="available" @selected(request('availability')=='available')>In stock only</option></select></div>
    <div class="col-md-2 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('borrower.equipment') }}" class="btn btn-light">Reset</a></div>
</form></div>

<div class="row g-3">
    @forelse($equipment as $e)
        @php($pct = $e->quantity > 0 ? round($e->available_quantity / $e->quantity * 100) : 0)
        <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
            <div class="card eq-card">
                <a href="{{ route('borrower.equipment.show', $e) }}" class="media" aria-label="{{ $e->name }}">
                    @if($e->image_url)<img src="{{ $e->image_url }}" alt="{{ $e->name }}" loading="lazy">@else<i class="bi bi-box-seam"></i>@endif
                </a>
                <div class="body">
                    <span class="badge-soft b-returned eq-cat">{{ $e->category->name }}</span>
                    <h6><a href="{{ route('borrower.equipment.show', $e) }}" class="text-dark">{{ $e->name }}</a></h6>
                    <p class="small text-muted flex-grow-1 mb-3">{{ \Illuminate\Support\Str::limit($e->description, 80) ?: 'No description.' }}</p>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold {{ $e->available_quantity == 0 ? 'text-danger' : '' }}">{{ $e->available_quantity > 0 ? $e->available_quantity.' available' : 'Out of stock' }}</span>
                        <span class="text-muted">of {{ $e->quantity }}</span>
                    </div>
                    <div class="stock-bar {{ $e->available_quantity == 0 ? 'none' : ($pct <= 25 ? 'low' : '') }} mb-3"><span style="width:{{ $pct }}%"></span></div>
                    @if($e->available_quantity > 0)
                        <a href="{{ route('borrower.requests.create', ['equipment' => $e->id]) }}" class="btn btn-primary w-100"><i class="bi bi-box-arrow-up-right me-1"></i> Borrow now</a>
                    @else
                        <button class="btn btn-light w-100 no-loading" disabled>Unavailable</button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card"><x-empty-state icon="search" message="No equipment matches your search." /></div></div>
    @endforelse
</div>
@if($equipment->hasPages())<div class="mt-4">{{ $equipment->links() }}</div>@endif
@endsection
