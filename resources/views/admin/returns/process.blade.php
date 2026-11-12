@extends('layouts.app')
@section('title', 'Process return')
@section('content')
@php($req = $transaction->request)
<x-page-header title="Process return" :subtitle="$req->request_no.' · '.$req->user->name">
    <a href="{{ route('admin.returns.index') }}" class="btn btn-light">Cancel</a>
</x-page-header>

<form method="POST" action="{{ route('admin.returns.store', $transaction) }}" novalidate
      data-confirm="Save this return? Stock will be updated and this cannot be repeated." data-confirm-title="Confirm return" data-confirm-button="Save return">
    @csrf
    <div class="card mb-3">
        <div class="card-header">Returned equipment</div>
        <div class="card-body">
            <p class="text-muted">For each item, the three quantities must add up to the amount borrowed. Damaged and missing units are removed from stock.</p>
            @foreach($req->items as $i)
                @php($k = $i->equipment_id)
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2"><strong>{{ $i->display_name }}</strong><span class="text-muted">Borrowed: {{ $i->quantity }}</span></div>
                    <div class="row g-2">
                        <div class="col-sm-3"><label class="form-label">Returned good</label>
                            <input type="number" min="0" max="{{ $i->quantity }}" name="items[{{ $k }}][good]" value="{{ old("items.$k.good", $i->quantity) }}" class="form-control"></div>
                        <div class="col-sm-3"><label class="form-label">Damaged</label>
                            <input type="number" min="0" max="{{ $i->quantity }}" name="items[{{ $k }}][damaged]" value="{{ old("items.$k.damaged", 0) }}" class="form-control"></div>
                        <div class="col-sm-3"><label class="form-label">Missing</label>
                            <input type="number" min="0" max="{{ $i->quantity }}" name="items[{{ $k }}][missing]" value="{{ old("items.$k.missing", 0) }}" class="form-control"></div>
                        <div class="col-sm-3"><label class="form-label">Condition</label>
                            <select name="items[{{ $k }}][condition]" class="form-select">
                                @foreach(['good' => 'Good', 'fair' => 'Fair', 'damaged' => 'Damaged'] as $v => $l)<option value="{{ $v }}" @selected(old("items.$k.condition", 'good') == $v)>{{ $l }}</option>@endforeach
                            </select></div>
                    </div>
                </div>
            @endforeach
            @foreach($errors->get('items.*') as $msgs) @foreach($msgs as $m)<div class="text-danger small">{{ $m }}</div>@endforeach @endforeach
        </div>
    </div>
    <div class="card"><div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label" for="actual_return_date">Actual return date</label>
                <input type="date" id="actual_return_date" name="actual_return_date" max="{{ now()->toDateString() }}" value="{{ old('actual_return_date', now()->toDateString()) }}" class="form-control @error('actual_return_date') is-invalid @enderror" required>
                @error('actual_return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($transaction->due_date->lt(now()->startOfDay()))<div class="form-text text-danger">Due {{ $transaction->due_date->format('M j, Y') }}, so this return is late.</div>@endif</div>
            <div class="col-md-8 mb-3"><label class="form-label" for="notes">Notes (optional)</label>
                <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
        </div>
        <button class="btn btn-primary">Save return</button>
    </div></div>
</form>
@endsection
