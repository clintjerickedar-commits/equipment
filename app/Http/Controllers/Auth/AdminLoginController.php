<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/** Separate sign-in page for administrators. Borrower accounts are never accepted here. */
class AdminLoginController extends Controller
{
    public function show()
    {
        return view('auth.admin-login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => config('services.recaptcha.secret') ? ['required', new Recaptcha] : ['nullable'],
        ], ['g-recaptcha-response.required' => 'Please tick the "I\'m not a robot" box.']);

        $user = User::where('email', $data['email'])->first();

        // One generic message for "no such user", "wrong password" and "not an admin" so nothing is revealed
        if (!$user || !$user->isAdmin() || !Hash::check($data['password'], $user->password)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Invalid administrator credentials.']);
        }

        if (!$user->is_active) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'This administrator account has been deactivated.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
