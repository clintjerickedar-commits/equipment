<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBorrowRequest;
use App\Models\BorrowingRequest;
use App\Models\Equipment;
use App\Models\Setting;
use App\Services\BorrowingService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct(private BorrowingService $borrowing) {}

    public function index(Request $request)
    {
        $requests = BorrowingRequest::with('items.equipment')
            ->where('user_id', auth()->id())
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('borrower.requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $equipment = Equipment::borrowable()->orderBy('name')->get(['id', 'name', 'equipment_code', 'available_quantity']);

        return view('borrower.requests.create', [
            'equipment'   => $equipment,
            'preselected' => $request->integer('equipment'),
            'maxDays'     => (int) Setting::get('max_borrow_days'),
            'maxQty'      => (int) Setting::get('max_quantity_per_item'),
        ]);
    }

    public function store(StoreBorrowRequest $request)
    {
        $this->borrowing->submit($request->user(), $request->validated());

        return redirect()->route('borrower.requests.index')
            ->with('success', 'Your borrowing request was submitted and is waiting for approval.');
    }

    public function show(BorrowingRequest $borrowingRequest)
    {
        abort_unless($borrowingRequest->user_id === auth()->id(), 403);
        $borrowingRequest->load('items.equipment', 'transaction.returnRecord.items.equipment');

        return view('borrower.requests.show', ['req' => $borrowingRequest]);
    }

    public function cancel(BorrowingRequest $borrowingRequest)
    {
        $this->borrowing->cancel($borrowingRequest, auth()->user());

        return redirect()->route('borrower.requests.index')->with('success', 'Request cancelled.');
    }
}
