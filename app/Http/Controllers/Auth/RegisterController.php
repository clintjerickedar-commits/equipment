<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // Role is always "borrower" for public registration (never taken from the form).
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'id_number' => $request->id_number,
            'phone'     => $request->phone,
            'password'  => $request->password, // hashed by the model cast
            'role'      => 'borrower',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Account created successfully.');
    }
}
