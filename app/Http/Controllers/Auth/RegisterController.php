<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;

class RegisterController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function show()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // Role is always "borrower" for public registration (never taken from the form).
        // email_verified_at stays NULL = unverified account.
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'id_number' => $request->id_number,
            'phone'     => $request->phone,
            'password'  => $request->password, // hashed by the model cast
            'role'      => 'borrower',
            'is_active' => true,
        ]);

        $request->session()->put('otp_user_id', $user->id);

        // The code is emailed to the address the user typed in the form
        if ($this->otp->send($user) === 'sent') {
            return redirect()->route('otp.show')->with('success', 'Account created! We sent a verification code to ' . OtpService::maskEmail($user->email) . '.');
        }

        // Only claim success when the email really was handed to the mail server
        return redirect()->route('otp.show')->with('error', 'Your account was created, but we could not send the verification email. Check your email address, then click "Resend code".');
    }
}
