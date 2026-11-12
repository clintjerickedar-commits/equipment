@extends('layouts.app')
@php($editing = $item->exists)
@section('title', $editing ? 'Edit equipment' : 'Add equipment')
@section('content')
<x-page-header :title="$editing ? 'Edit equipment' : 'Add equipment'" :subtitle="$editing ? $item->equipment_code : 'A code is generated automatically.'">
    <a href="{{ route('admin.equipment.index') }}" class="btn btn-light">Cancel</a>
</x-page-header>

<form method="POST" enctype="multipart/form-data" novalidate
      action="{{ $editing ? route('admin.equipment.update', $item) : route('admin.equipment.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card"><div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="name">Equipment name</label>
                    <input id="name" name="name" value="{{ old('name', $item->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Choose a category</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $item->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($categories->isEmpty())<div class="form-text text-danger">Create a category first.</div>@endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="quantity">Total quantity</label>
                        <input type="number" min="0" id="quantity" name="quantity" value="{{ old('quantity', $item->quantity) }}" class="form-control @error('quantity') is-invalid @enderror" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($editing)<div class="form-text">{{ $item->borrowed_quantity }} unit(s) are currently borrowed.</div>@endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="condition">Condition</label>
                        <select id="condition" name="condition" class="form-select">
                            @foreach(\App\Models\Equipment::CONDITIONS as $k => $v)<option value="{{ $k }}" @selected(old('condition', $item->condition) == $k)>{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(\App\Models\Equipment::STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('status', $item->status) == $k)>{{ $v }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-body">
                <label class="form-label" for="image">Photo</label>
                @if($item->image_url)<img src="{{ $item->image_url }}" class="img-fluid rounded mb-3" alt="{{ $item->name }}">@endif
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp" class="form-control @error('image') is-invalid @enderror">
                <div class="form-text">JPG, PNG or WebP, up to 2 MB.</div>
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div></div>
            <button class="btn btn-primary w-100 mt-3">{{ $editing ? 'Save changes' : 'Add equipment' }}</button>
        </div>
    </div>
</form>
@endsection
