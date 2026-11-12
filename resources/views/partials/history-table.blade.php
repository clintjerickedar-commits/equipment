{{-- Shared by admin history, borrower history and the borrower profile page. Expects $history (paginator) --}}
@php($showBorrower = $showBorrower ?? true)
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr>
            <th>Request</th>@if($showBorrower)<th>Borrower</th>@endif<th>Equipment</th><th>Qty</th>
            <th>Borrowed</th><th>Expected return</th><th>Actual return</th><th>Status</th>
        </tr></thead>
        <tbody>
        @forelse($history as $r)
            <tr>
                <td class="fw-semibold">{{ $r->request_no }}</td>
                @if($showBorrower)<td>{{ $r->user->name }}</td>@endif
                <td>@foreach($r->items as $i)<div>{{ $i->display_name }}</div>@endforeach</td>
                <td>@foreach($r->items as $i)<div>{{ $i->quantity }}</div>@endforeach</td>
                <td>{{ $r->borrow_date->format('M j, Y') }}</td>
                <td>{{ $r->expected_return_date->format('M j, Y') }}</td>
                <td>{{ $r->actual_return_date?->format('M j, Y') ?? '—' }}</td>
                <td><x-status-badge :status="$r->display_status" /></td>
            </tr>
        @empty
            <tr><td colspan="8"><x-empty-state icon="clock-history" message="No borrowing records match your filters." /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if($history->hasPages())<div class="p-3 border-top">{{ $history->links() }}</div>@endif
