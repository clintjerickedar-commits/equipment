<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowingRequest;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public const TYPES = [
        'inventory'    => 'Equipment inventory',
        'transactions' => 'Borrowing transactions',
        'pending'      => 'Pending requests',
        'approved'     => 'Approved requests',
        'returned'     => 'Returned equipment',
        'overdue'      => 'Overdue equipment',
    ];

    public function index(Request $request)
    {
        $type = array_key_exists($request->type, self::TYPES) ? $request->type : 'transactions';
        $rows = collect();

        if ($type === 'inventory') {
            $rows = Equipment::with('category')
                ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->orderBy('name')->get();
        } else {
            $query = BorrowingRequest::with('user', 'items.equipment', 'transaction.returnRecord')
                ->when($request->from, fn ($q, $d) => $q->whereDate('borrow_date', '>=', $d))
                ->when($request->to, fn ($q, $d) => $q->whereDate('borrow_date', '<=', $d));

            match ($type) {
                'pending'  => $query->where('status', 'pending'),
                'approved' => $query->where('status', 'approved'),
                'returned' => $query->where('status', 'returned'),
                'overdue'  => $query->late(),
                // "transactions" report can be narrowed by any status
                default    => $query->when($request->status, fn ($q, $s) => $s === 'overdue' ? $q->late() : $q->where('status', $s)),
            };

            $rows = $query->latest('borrow_date')->get();
        }

        return view('admin.reports', [
            'type'       => $type,
            'types'      => self::TYPES,
            'rows'       => $rows,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
