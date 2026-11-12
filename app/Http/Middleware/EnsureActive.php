<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Blocks deactivated accounts. */
class EnsureActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $error = null;
            if (!$user->is_active) {
                $error = 'Your account has been deactivated. Contact an administrator.';
            }

            if ($error) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => $error]);
            }
        }

        return $next($request);
    }
}
