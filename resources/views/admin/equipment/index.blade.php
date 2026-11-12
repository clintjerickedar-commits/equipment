@extends('layouts.app')
@section('title', 'Equipment')
@section('content')
<x-page-header title="Equipment" subtitle="Manage the inventory that borrowers can request.">
    <a href="{{ route('admin.equipment.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add equipment</a>
</x-page-header>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or code"></div>
            <div class="col-md-3">
                <select name="category" class="form-select"><option value="">All categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select"><option value="">All statuses</option>
                    @foreach(\App\Models\Equipment::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') == $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.equipment.index') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Equipment</th><th>Category</th><th class="text-center">Total</th><th class="text-center">Available</th><th class="text-center">Borrowed</th><th>Condition</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($equipment as $e)
                <tr>
                    <td><div class="d-flex align-items-center gap-3"><x-equipment-thumb :item="$e" />
                        <div><a href="{{ route('admin.equipment.show', $e) }}" class="fw-semibold">{{ $e->name }}</a><div class="text-muted small">{{ $e->equipment_code }}</div></div></div></td>
                    <td>{{ $e->category->name }}</td>
                    <td class="text-center">{{ $e->quantity }}</td>
                    <td class="text-center"><span class="fw-semibold {{ $e->available_quantity == 0 ? 'text-danger' : '' }}">{{ $e->available_quantity }}</span></td>
                    <td class="text-center">{{ $e->borrowed_quantity }}</td>
                    <td>{{ \App\Models\Equipment::CONDITIONS[$e->condition] ?? $e->condition }}</td>
                    <td><x-status-badge :status="$e->status" /></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.equipment.show', $e) }}" class="btn btn-sm btn-icon btn-light" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.equipment.edit', $e) }}" class="btn btn-sm btn-icon btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.equipment.destroy', $e) }}" class="d-inline"
                              data-confirm="Are you sure you want to delete this equipment?" data-confirm-name="{{ $e->name }} ({{ $e->equipment_code }})" data-confirm-note="Its borrowing history stays on record." data-confirm-title="Delete Equipment?" data-confirm-button="Delete">
                            @csrf @method('DELETE')
                            <input type="hidden" name="page" value="{{ $equipment->currentPage() }}">
                            <input type="hidden" name="q" value="{{ request('q') }}">
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <button class="btn btn-sm btn-icon btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty-state icon="box-seam" message="No equipment found."><a href="{{ route('admin.equipment.create') }}" class="btn btn-primary btn-sm mt-3">Add the first item</a></x-empty-state></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($equipment->hasPages())<div class="p-3 border-top">{{ $equipment->links() }}</div>@endif
</div>
@endsection
