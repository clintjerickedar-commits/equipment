<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowingTransaction;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returns) {}

    /** Approved transactions that are still out (active or overdue). */
    public function index(Request $request)
    {
        $transactions = BorrowingTransaction::with('request.user', 'request.items.equipment')
            ->whereIn('status', ['active', 'overdue'])
            ->when($request->filter === 'overdue', fn ($q) => $q->whereDate('due_date', '<', now()->toDateString()))
            ->when($request->q, fn ($q, $s) => $q->whereHas('request', fn ($r) => $r->where('request_no', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))))
            ->orderBy('due_date')->paginate(10)->withQueryString();

        return view('admin.returns.index', compact('transactions'));
    }

    public function create(BorrowingTransaction $transaction)
    {
        if ($transaction->status === 'returned') {
            return redirect()->route('admin.returns.index')->with('error', 'This transaction was already returned.');
        }

        $transaction->load('request.user', 'request.items.equipment');
        return view('admin.returns.process', compact('transaction'));
    }

    public function store(Request $request, BorrowingTransaction $transaction)
    {
        $data = $request->validate([
            'actual_return_date'  => ['required', 'date', 'before_or_equal:today'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'items'               => ['required', 'array'],
            'items.*.good'        => ['required', 'integer', 'min:0'],
            'items.*.damaged'     => ['required', 'integer', 'min:0'],
            'items.*.missing'     => ['required', 'integer', 'min:0'],
            'items.*.condition'   => ['required', 'in:good,fair,damaged'],
        ]);

        $this->returns->process($transaction, $data, $request->user());

        return redirect()->route('admin.returns.index')->with('success', 'Return recorded and stock updated.');
    }
}
