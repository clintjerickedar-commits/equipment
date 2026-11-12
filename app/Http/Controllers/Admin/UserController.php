<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** User accounts: create administrators/borrowers, edit, activate/deactivate, delete. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('borrowingRequests')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")->orWhere('id_number', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('role')->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'admin', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'role'      => ['required', Rule::in(['admin', 'borrower'])],
            'id_number' => ['nullable', 'string', 'max:50', 'unique:users,id_number'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Accounts created by an administrator are trusted, so they skip email verification
        User::create($data + ['is_active' => true, 'email_verified_at' => now()]);

        return redirect()->route('admin.users.index')->with('success', 'Account created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in(['admin', 'borrower'])],
            'id_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'id_number')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // You cannot change your own role (prevents locking yourself out)
        if ($user->id === $request->user()->id) {
            $data['role'] = $user->role;
        }
        if (empty($data['password'])) {
            unset($data['password']); // blank = keep the current password
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Account updated.');
    }

    public function toggle(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->borrowingRequests()->exists()) {
            return back()->with('error', 'This user has borrowing history and cannot be deleted. Deactivate the account instead.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Account deleted.');
    }
}
