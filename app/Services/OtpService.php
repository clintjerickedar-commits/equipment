<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email verification codes.
 *  - 6 random digits, stored HASHED, valid for 10 minutes
 *  - sent to the address stored on the user (the one typed at registration)
 *  - a new code overwrites (invalidates) the previous one
 *  - 60-second resend cooldown, 5 wrong guesses per code
 *  - a code is cleared after successful use, so it cannot be reused
 */
class OtpService
{
    public const TTL_MINUTES      = 10;
    public const COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS     = 5;

    /** @return string 'sent' | 'cooldown' | 'failed' */
    public function send(User $user): string
    {
        if ($this->cooldownRemaining($user) > 0) {
            return 'cooldown';
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'otp_code'       => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'otp_attempts'   => 0,
        ])->save();

        try {
            // Sent synchronously so a failure is detected and never reported as success
            Mail::to($user->email)->send(new VerificationCodeMail($user, $code, self::TTL_MINUTES));
        } catch (\Throwable $e) {
            Log::error("Verification email failed for user #{$user->id}: " . $e->getMessage());
            $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();
            return 'failed';
        }

        $user->forceFill(['otp_sent_at' => now()])->save();
        return 'sent';
    }

    /** Sends a code only if the user has no valid one (used after a blocked login). */
    public function ensureCode(User $user): void
    {
        if (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            $this->send($user);
        }
    }

    /** @return string 'ok' | 'expired' | 'locked' | 'invalid' */
    public function verify(User $user, string $code): string
    {
        if (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return 'expired';
        }
        if ($user->otp_attempts >= self::MAX_ATTEMPTS) {
            return 'locked';
        }

        if (!Hash::check($code, $user->otp_code)) {
            $user->forceFill(['otp_attempts' => $user->otp_attempts + 1])->save();
            return $user->otp_attempts >= self::MAX_ATTEMPTS ? 'locked' : 'invalid';
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            'otp_attempts'      => 0,
        ])->save();

        return 'ok';
    }

    public function cooldownRemaining(User $user): int
    {
        if (!$user->otp_sent_at) {
            return 0;
        }
        return max(0, self::COOLDOWN_SECONDS - (now()->timestamp - $user->otp_sent_at->timestamp));
    }

    public static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = strlen($local) > 2 ? substr($local, 0, 1) . str_repeat('*', max(3, strlen($local) - 2)) . substr($local, -1) : substr($local, 0, 1) . '***';
        return $visible . '@' . $domain;
    }
}
