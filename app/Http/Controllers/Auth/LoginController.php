<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => config('services.recaptcha.secret') ? ['required', new Recaptcha] : ['nullable'],
        ], ['g-recaptcha-response.required' => 'Please tick the "I\'m not a robot" box.']);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->is_active === false) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account has been deactivated. Contact an administrator.']);
        }

        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Invalid email or password.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        $wasAdmin = Auth::user()?->isAdmin();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasAdmin ? 'admin.login' : 'home')->with('success', 'You have been logged out.');
    }
}
