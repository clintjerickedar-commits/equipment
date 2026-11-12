<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = BorrowingRequest::with('user', 'items.equipment', 'transaction.returnRecord')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'overdue', 'returned'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->whereHas('items.equipment', fn ($e) => $e->where('name', 'like', "%{$s}%"))
                ->orWhereHas('items', fn ($i) => $i->where('equipment_name', 'like', "%{$s}%"))))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->from, fn ($q, $d) => $q->whereDate('borrow_date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('borrow_date', '<=', $d))
            ->latest('borrow_date')->paginate(10)->withQueryString();

        return view('borrower.history', compact('history'));
    }
}
