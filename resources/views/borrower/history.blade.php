@extends('layouts.app')
@section('title', 'My history')
@section('content')
<x-page-header title="My borrowing history" subtitle="Items you have borrowed and returned." />
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search equipment"></div>
            <div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>
                @foreach(['approved','overdue','returned'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From"></div>
            <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To"></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-navy flex-fill">Filter</button><a href="{{ route('borrower.history') }}" class="btn btn-light">Reset</a></div>
        </form>
    </div>
    @include('partials.history-table', ['history' => $history, 'showBorrower' => false])
</div>
@endsection
