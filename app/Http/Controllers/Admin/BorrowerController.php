<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BorrowerController extends Controller
{
    public function index(Request $request)
    {
        $borrowers = User::borrowers()->withCount('borrowingRequests')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")->orWhere('id_number', 'like', "%{$s}%")))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.borrowers.index', compact('borrowers'));
    }

    public function show(User $borrower)
    {
        abort_unless($borrower->role === 'borrower', 404);

        $history = $borrower->borrowingRequests()->with('user', 'items.equipment', 'transaction.returnRecord')
            ->latest()->paginate(10);

        return view('admin.borrowers.show', compact('borrower', 'history'));
    }

    public function edit(User $borrower)
    {
        abort_unless($borrower->role === 'borrower', 404);
        return view('admin.borrowers.edit', compact('borrower'));
    }

    public function update(Request $request, User $borrower)
    {
        abort_unless($borrower->role === 'borrower', 404);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($borrower->id)],
            'id_number' => ['required', 'string', 'max:50', Rule::unique('users', 'id_number')->ignore($borrower->id)],
            'phone'     => ['nullable', 'string', 'max:30'],
        ]);

        $borrower->update($data);

        return redirect()->route('admin.borrowers.show', $borrower)->with('success', 'Borrower updated.');
    }

    public function toggle(User $borrower)
    {
        abort_unless($borrower->role === 'borrower', 404);

        $borrower->update(['is_active' => !$borrower->is_active]);

        return back()->with('success', $borrower->is_active ? 'Account activated.' : 'Account deactivated.');
    }
}
