<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/** Profile & settings for any logged-in user (admin or borrower). */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'id_number' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'id_number')->ignore($request->user()->id)],
        ]);

        // Email is read-only: it is the verified login identity
        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.different'                => 'The new password must be different from the current one.',
        ]);

        $request->user()->update(['password' => $request->password]); // hashed by the model cast

        return back()->with('success', 'Password changed.');
    }
}
