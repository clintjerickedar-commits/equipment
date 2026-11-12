<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Email verification page: enter code, resend code. */
class OtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    private function pendingUser(Request $request): ?User
    {
        return User::find($request->session()->get('otp_user_id'));
    }

    public function show(Request $request)
    {
        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->email_verified_at) {
            $request->session()->forget('otp_user_id');
            return redirect()->route('login')->with('status', 'Your email is already verified. Please log in.');
        }

        return view('auth.otp', [
            'maskedEmail' => OtpService::maskEmail($user->email),
            'expiresAt'   => $user->otp_expires_at?->isFuture() ? $user->otp_expires_at->timestamp : null,
            'cooldown'    => $this->otp->cooldownRemaining($user),
            'mailIsLocal' => in_array(config('mail.default'), ['log', 'array'], true),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']], ['code.*' => 'Enter the 6-digit code from your email.']);

        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        $result = $this->otp->verify($user, $request->code);

        if ($result === 'ok') {
            $request->session()->forget('otp_user_id');
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Email verified! Welcome to the Equipment Borrowing System.');
        }

        $left = max(0, OtpService::MAX_ATTEMPTS - $user->fresh()->otp_attempts);
        $message = match ($result) {
            'expired' => 'This code has expired. Click "Resend code" to get a new one.',
            'locked'  => 'Too many incorrect attempts. Click "Resend code" to get a new one.',
            default   => "That code is incorrect. You have {$left} attempt(s) left.",
        };

        return back()->withErrors(['code' => $message]);
    }

    public function resend(Request $request)
    {
        $user = $this->pendingUser($request);
        if (!$user) {
            return redirect()->route('login');
        }

        return match ($this->otp->send($user)) {
            'sent'     => back()->with('success', 'A new code was sent to ' . OtpService::maskEmail($user->email) . '. The previous code no longer works.'),
            'cooldown' => back()->with('error', 'Please wait ' . $this->otp->cooldownRemaining($user) . ' seconds before requesting another code.'),
            default    => back()->with('error', 'We could not send the email right now. Please try again in a moment or contact the administrator.'),
        };
    }
}
