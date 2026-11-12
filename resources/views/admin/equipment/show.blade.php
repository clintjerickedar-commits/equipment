@extends('layouts.app')
@section('title', $item->name)
@section('content')
<x-page-header :title="$item->name" :subtitle="$item->equipment_code.' · '.$item->category->name">
    <a href="{{ route('admin.equipment.index') }}" class="btn btn-light">Back</a>
    <a href="{{ route('admin.equipment.edit', $item) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
</x-page-header>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card overflow-hidden">
            <div class="eq-hero">@if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->name }}">@else<i class="bi bi-box-seam"></i>@endif</div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-4"><x-stat-card icon="box-seam" tone="navy" label="Total" :value="$item->quantity" /></div>
            <div class="col-4"><x-stat-card icon="check2-circle" tone="green" label="Available" :value="$item->available_quantity" /></div>
            <div class="col-4"><x-stat-card icon="box-arrow-up-right" tone="blue" label="Borrowed" :value="$item->borrowed_quantity" /></div>
        </div>
        <div class="card"><div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><x-status-badge :status="$item->status" /></dd>
                <dt class="col-sm-3">Condition</dt><dd class="col-sm-9">{{ \App\Models\Equipment::CONDITIONS[$item->condition] ?? $item->condition }}</dd>
                <dt class="col-sm-3">Description</dt><dd class="col-sm-9 mb-0">{{ $item->description ?: 'No description.' }}</dd>
            </dl>
        </div></div>
    </div>
</div>
@endsection
