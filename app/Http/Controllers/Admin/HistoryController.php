<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = BorrowingRequest::with('user', 'items.equipment', 'transaction.returnRecord')
            ->whereIn('status', ['approved', 'overdue', 'returned'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('request_no', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                ->orWhereHas('items.equipment', fn ($e) => $e->where('name', 'like', "%{$s}%"))
                ->orWhereHas('items', fn ($i) => $i->where('equipment_name', 'like', "%{$s}%"))))
            ->when($request->status === 'overdue', fn ($q) => $q->late())
            ->when($request->status && $request->status !== 'overdue', fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q, $d) => $q->whereDate('borrow_date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('borrow_date', '<=', $d))
            ->latest('borrow_date')->paginate(15)->withQueryString();

        return view('admin.history', compact('history'));
    }
}
