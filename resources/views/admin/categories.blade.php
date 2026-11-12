@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<x-page-header title="Categories" subtitle="Group equipment so borrowers can find it faster.">
    <button class="btn btn-primary no-loading" data-bs-toggle="modal" data-bs-target="#addCategory"><i class="bi bi-plus-lg"></i> Add category</button>
</x-page-header>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Name</th><th>Description</th><th>Equipment</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($categories as $c)
                <tr>
                    <td class="fw-semibold">{{ $c->name }}</td>
                    <td class="text-muted">{{ $c->description ?: '—' }}</td>
                    <td>{{ $c->equipment_count }}</td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-icon btn-light no-loading" data-bs-toggle="modal" data-bs-target="#edit{{ $c->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline"
                              data-confirm="Delete the {{ $c->name }} category?" data-confirm-title="Delete category" data-confirm-button="Delete">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-icon btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-empty-state icon="tags" message="No categories yet." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($categories as $c)

                <div class="modal fade" id="edit{{ $c->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('admin.categories.update', $c) }}" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Edit category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ $c->name }}" class="form-control" required></div>
                            <div><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ $c->description }}</textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light no-loading" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save changes</button></div>
                    </form></div>
                </div>
@endforeach

<div class="modal fade" id="addCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('admin.categories.store') }}" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Add category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Name</label><input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
            <div><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light no-loading" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add category</button></div>
    </form></div>
</div>
@if($errors->has('name'))
    @push('scripts')<script>new bootstrap.Modal(document.getElementById('addCategory')).show();</script>@endpush
@endif
@endsection
