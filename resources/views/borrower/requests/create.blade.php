@extends('layouts.app')
@section('title', 'New request')
@section('content')
<x-page-header title="New borrowing request" subtitle="Choose the equipment, dates and purpose.">
    <a href="{{ route('borrower.equipment') }}" class="btn btn-light">Browse equipment</a>
</x-page-header>

@if($equipment->isEmpty())
    <div class="card"><x-empty-state icon="box-seam" message="No equipment is available right now. Please check back later." /></div>
@else
<form method="POST" action="{{ route('borrower.requests.store') }}" novalidate>
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Equipment</div><div class="card-body">
                <div id="rows"></div>
                <button type="button" class="btn btn-outline-primary btn-sm no-loading" id="addRow"><i class="bi bi-plus-lg"></i> Add another item</button>
                @error('items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                @foreach($errors->get('items.*') as $msgs) @foreach($msgs as $m)<div class="text-danger small mt-1">{{ $m }}</div>@endforeach @endforeach
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card"><div class="card-header">Details</div><div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label" for="borrow_date">Borrow date</label>
                        <input type="date" id="borrow_date" name="borrow_date" min="{{ now()->toDateString() }}" value="{{ old('borrow_date', now()->toDateString()) }}" class="form-control @error('borrow_date') is-invalid @enderror" required>
                        @error('borrow_date') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                    <div class="col-6 mb-3"><label class="form-label" for="expected_return_date">Return date</label>
                        <input type="date" id="expected_return_date" name="expected_return_date" min="{{ now()->toDateString() }}" value="{{ old('expected_return_date') }}" class="form-control @error('expected_return_date') is-invalid @enderror" required>
                        @error('expected_return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($maxDays > 0)<div class="form-text">Up to {{ $maxDays }} day(s).</div>@endif</div>
                </div>
                <div class="mb-3"><label class="form-label" for="purpose">Purpose</label>
                    <textarea id="purpose" name="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror" placeholder="What will you use the equipment for?" required>{{ old('purpose') }}</textarea>
                    @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                <button class="btn btn-primary w-100">Submit request</button>
            </div></div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const catalog = @json($equipment);                       // [{id, name, equipment_code, available_quantity}]
    const oldItems = @json(old('items', []));
    const preselected = {{ (int) $preselected }};
    const maxQty = {{ (int) $maxQty }}, maxDays = {{ (int) $maxDays }};   // 0 = no limit (set by the administrator)
    const rows = document.getElementById('rows');
    let index = 0;

    function addRow(equipmentId = '', qty = 1) {
        const i = index++;
        const options = catalog.map(e => `<option value="${e.id}" data-max="${e.available_quantity}" ${e.id == equipmentId ? 'selected' : ''}>${e.name} (${e.available_quantity} available)</option>`).join('');
        const wrap = document.createElement('div');
        wrap.className = 'row g-2 align-items-end mb-3 item-row';
        wrap.innerHTML = `
            <div class="col-8"><label class="form-label">Item</label>
                <select name="items[${i}][equipment_id]" class="form-select" required><option value="">Choose equipment</option>${options}</select></div>
            <div class="col-3"><label class="form-label">Qty</label>
                <input type="number" min="1" name="items[${i}][quantity]" value="${qty}" class="form-control" required></div>
            <div class="col-1"><button type="button" class="btn btn-light text-danger no-loading remove" title="Remove"><i class="bi bi-x-lg"></i></button></div>`;
        rows.appendChild(wrap);
        const select = wrap.querySelector('select'), input = wrap.querySelector('input[type=number]');
        const syncMax = () => { let max = parseInt(select.selectedOptions[0]?.dataset.max || 0, 10); if (max && maxQty > 0) max = Math.min(max, maxQty); else if (!max && maxQty > 0) max = maxQty; if (max) input.max = max; else input.removeAttribute('max'); };
        select.addEventListener('change', syncMax); syncMax();
        wrap.querySelector('.remove').addEventListener('click', () => { if (rows.children.length > 1) wrap.remove(); });
    }

    document.getElementById('addRow').addEventListener('click', () => addRow());
    const start = Object.values(oldItems);
    if (start.length) start.forEach(it => addRow(it.equipment_id, it.quantity)); else addRow(preselected || '');

    // Keep the return date from being earlier than the borrow date
    const b = document.getElementById('borrow_date'), r = document.getElementById('expected_return_date');
    const syncDates = () => {
        r.min = b.value; if (r.value && r.value < b.value) r.value = b.value;
        if (maxDays > 0 && b.value) { const d = new Date(b.value); d.setDate(d.getDate() + maxDays); r.max = d.toISOString().slice(0, 10); }
    };
    b.addEventListener('change', syncDates); syncDates();
</script>
@endpush
@endif
@endsection
