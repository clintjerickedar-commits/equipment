@extends('layouts.app')
@section('title', 'Borrowing history')
@section('content')
<x-page-header title="Borrowing history" subtitle="Every approved, overdue and returned borrowing." />
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Borrower, equipment or request no."></div>
            <div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>
                @foreach(['approved','overdue','returned'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To"></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('admin.history') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    @include('partials.history-table', ['history' => $history])
</div>
@endsection
