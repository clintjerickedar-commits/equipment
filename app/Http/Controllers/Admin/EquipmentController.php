<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\BorrowingRequest;
use App\Models\BorrowingRequestItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EquipmentController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $equipment = Equipment::with('category')
            ->filter($request->only('q', 'category', 'status'))
            ->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.equipment.index', [
            'equipment'  => $equipment,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.equipment.form', ['item' => new Equipment(['condition' => 'good', 'status' => 'active']), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(EquipmentRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['equipment_code']    = Equipment::nextCode();
        $data['available_quantity'] = $data['quantity']; // brand-new stock is all available

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('equipment', 'public');
        }

        Equipment::create($data);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipment added.');
    }

    public function show(Equipment $equipment)
    {
        $equipment->load('category');
        return view('admin.equipment.show', ['item' => $equipment]);
    }

    public function edit(Equipment $equipment)
    {
        return view('admin.equipment.form', ['item' => $equipment, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(EquipmentRequest $request, Equipment $equipment)
    {
        $data = $request->safe()->except('image');

        // Units currently borrowed must stay covered by the total quantity
        $borrowed = $equipment->borrowed_quantity;
        if ($data['quantity'] < $borrowed) {
            throw ValidationException::withMessages([
                'quantity' => "{$borrowed} unit(s) are currently borrowed, so the total quantity cannot be lower than {$borrowed}.",
            ]);
        }
        $data['available_quantity'] = $data['quantity'] - $borrowed;

        if ($request->hasFile('image')) {
            if ($equipment->image_path) {
                Storage::disk('public')->delete($equipment->image_path);
            }
            $data['image_path'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment->update($data);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipment updated.');
    }

    public function destroy(Request $request, Equipment $equipment)
    {
        // Units that are out with a borrower must come back first, otherwise the return could not restock them.
        $unitsOut = (int) BorrowingRequestItem::where('equipment_id', $equipment->id)
            ->whereHas('request', fn ($q) => $q->outstanding())->sum('quantity');

        if ($unitsOut > 0) {
            return back()->with('error', "\"{$equipment->name}\" cannot be deleted yet: {$unitsOut} unit(s) are currently borrowed. Process their return first.");
        }

        $name     = $equipment->name;
        $image    = $equipment->image_path;
        $rejected = 0;

        try {
            DB::transaction(function () use ($equipment, $request, $name, &$rejected) {
                // Pending requests for this item can no longer be approved: reject them with a clear reason.
                $pending = BorrowingRequest::where('status', 'pending')
                    ->whereHas('items', fn ($q) => $q->where('equipment_id', $equipment->id))->lockForUpdate()->get();

                foreach ($pending as $pendingRequest) {
                    $pendingRequest->update([
                        'status'           => 'rejected',
                        'rejection_reason' => "The equipment \"{$name}\" was removed from the inventory.",
                        'reviewed_by'      => $request->user()->id,
                        'reviewed_at'      => now(),
                    ]);
                    $rejected++;
                }

                // Only this row is deleted. Request items and return items keep their rows (equipment_id becomes NULL
                // through ON DELETE SET NULL) and keep the saved name and code.
                $equipment->delete();
            });
        } catch (QueryException $e) {
            report($e);
            $reason = config('app.debug') ? ($e->errorInfo[2] ?? $e->getMessage()) : 'a database error occurred (details are in storage/logs/laravel.log).';
            return back()->with('error', "Equipment could not be deleted: {$reason}");
        }

        if ($image) {
            Storage::disk('public')->delete($image); // only after the row is really gone
        }

        // Stay on the same filtered list; if that page no longer exists (last item of the last page), go to the new last page.
        $filters  = array_filter($request->only('q', 'category', 'status'), fn ($v) => $v !== null && $v !== '');
        $lastPage = max(1, (int) ceil(Equipment::filter($filters)->count() / self::PER_PAGE));
        $page     = min(max(1, (int) $request->input('page', 1)), $lastPage);

        return redirect()
            ->route('admin.equipment.index', $filters + ($page > 1 ? ['page' => $page] : []))
            ->with('success', 'Equipment deleted successfully!' . ($rejected ? " {$rejected} pending request(s) for it were rejected." : ''));
    }
}
