<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $equipment = Equipment::with('category')
            ->where('status', 'active')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('equipment_code', 'like', "%{$s}%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->availability === 'available', fn ($q) => $q->where('available_quantity', '>', 0))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('borrower.equipment', [
            'equipment'  => $equipment,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Equipment $equipment)
    {
        abort_unless($equipment->status === 'active', 404);

        return view('borrower.equipment-show', ['item' => $equipment->load('category')]);
    }
}
