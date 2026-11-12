<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use App\Services\BorrowingService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct(private BorrowingService $borrowing) {}

    public function index(Request $request)
    {
        $requests = BorrowingRequest::with('user', 'items.equipment')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('request_no', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))))
            ->when($request->status === 'overdue', fn ($q) => $q->late())
            ->when($request->status && $request->status !== 'overdue', fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q, $d) => $q->whereDate('borrow_date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('borrow_date', '<=', $d))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.requests.index', compact('requests'));
    }

    public function show(BorrowingRequest $borrowingRequest)
    {
        $borrowingRequest->load('user', 'reviewer', 'items.equipment', 'transaction.returnRecord.items.equipment');
        return view('admin.requests.show', ['req' => $borrowingRequest]);
    }

    public function approve(BorrowingRequest $borrowingRequest)
    {
        $this->borrowing->approve($borrowingRequest, auth()->user());

        return redirect()->route('admin.requests.show', $borrowingRequest)->with('success', 'Request approved. Stock was updated.');
    }

    public function reject(Request $request, BorrowingRequest $borrowingRequest)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'min:5', 'max:500']]);

        $this->borrowing->reject($borrowingRequest, auth()->user(), $data['rejection_reason']);

        return redirect()->route('admin.requests.show', $borrowingRequest)->with('success', 'Request rejected.');
    }
}
