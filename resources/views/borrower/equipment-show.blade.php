@extends('layouts.app')
@section('title', $item->name)
@section('content')
@php($pct = $item->quantity > 0 ? round($item->available_quantity / $item->quantity * 100) : 0)
<x-page-header :title="$item->name" :subtitle="$item->equipment_code.' · '.$item->category->name">
    <a href="{{ route('borrower.equipment') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
</x-page-header>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card overflow-hidden">
            <div class="eq-hero">@if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->name }}">@else<i class="bi bi-box-seam"></i>@endif</div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge-soft b-returned">{{ $item->category->name }}</span>
                <span class="badge-soft {{ $item->available_quantity > 0 ? 'b-approved' : 'b-rejected' }}">{{ $item->available_quantity > 0 ? 'Available' : 'Out of stock' }}</span>
                <span class="badge-soft b-cancelled">Condition: {{ \App\Models\Equipment::CONDITIONS[$item->condition] ?? $item->condition }}</span>
            </div>
            <p class="mb-4">{{ $item->description ?: 'No description provided.' }}</p>
            <div class="d-flex justify-content-between small mb-1"><span class="fw-semibold">{{ $item->available_quantity }} available</span><span class="text-muted">of {{ $item->quantity }} total</span></div>
            <div class="stock-bar {{ $item->available_quantity == 0 ? 'none' : ($pct <= 25 ? 'low' : '') }} mb-4"><span style="width:{{ $pct }}%"></span></div>
            @if($item->available_quantity > 0)
                <a href="{{ route('borrower.requests.create', ['equipment' => $item->id]) }}" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-up-right me-1"></i> Borrow now</a>
            @else
                <button class="btn btn-light btn-lg no-loading" disabled>Currently unavailable</button>
            @endif
        </div></div>
    </div>
</div>
@endsection
