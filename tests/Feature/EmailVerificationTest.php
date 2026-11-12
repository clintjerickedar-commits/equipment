<?php

namespace Tests\Feature;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email = 'student123@gmail.com', string $id = '2024-9999')
    {
        return $this->post('/register', [
            'name' => 'Test Student', 'email' => $email, 'id_number' => $id, 'phone' => '0917',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ]);
    }

    /** Registers and returns the 6-digit code that was emailed. */
    private function registerAndGetCode(string $email = 'student123@gmail.com'): string
    {
        Mail::fake();
        $this->register($email)->assertRedirect(route('otp.show'));
        $code = null;
        Mail::assertSent(VerificationCodeMail::class, function ($mail) use (&$code) { $code = $mail->code; return true; });
        return $code;
    }

    public function test_code_is_emailed_to_the_exact_address_entered(): void
    {
        $code = $this->registerAndGetCode('student123@gmail.com');

        Mail::assertSent(VerificationCodeMail::class, fn ($m) => $m->hasTo('student123@gmail.com') && !$m->hasTo('admin@demo.test'));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $user = User::where('email', 'student123@gmail.com')->first();
        $this->assertNull($user->email_verified_at);               // created unverified
        $this->assertNotSame($code, $user->otp_code);              // stored hashed, not plain
    }

    public function test_correct_code_verifies_and_logs_in(): void
    {
        $code = $this->registerAndGetCode();
        $this->post('/verify-otp', ['code' => $code])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'student123@gmail.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->otp_code);                        // cannot be reused
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_code_is_rejected_and_attempts_are_limited(): void
    {
        $code = $this->registerAndGetCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/verify-otp', ['code' => $wrong])->assertSessionHasErrors('code');
        }
        // After 5 misses even the correct code is refused until a new one is requested
        $this->post('/verify-otp', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_code_is_rejected(): void
    {
        $code = $this->registerAndGetCode();
        $this->travel(11)->minutes();
        $this->post('/verify-otp', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_has_cooldown_then_replaces_the_old_code(): void
    {
        $old = $this->registerAndGetCode();

        $this->post('/verify-otp/resend')->assertSessionHas('error');   // inside 60 s: blocked
        Mail::assertSentCount(1);

        $this->travel(61)->seconds();
        $this->post('/verify-otp/resend')->assertSessionHas('success');
        Mail::assertSentCount(2);

        $new = null;
        Mail::assertSent(VerificationCodeMail::class, function ($m) use (&$new) { $new = $m->code; return true; });
        // the first code no longer works (unless the random codes happen to match)
        if ($new !== $old) {
            $this->post('/verify-otp', ['code' => $old])->assertSessionHasErrors('code');
        }
    }

    public function test_duplicate_email_and_id_number_are_rejected(): void
    {
        Mail::fake();
        $this->register('dup@gmail.com', 'ID-1')->assertRedirect(route('otp.show'));
        $this->post('/logout');

        $this->register('dup@gmail.com', 'ID-2')->assertSessionHasErrors('email');
        $this->register('other@gmail.com', 'ID-1')->assertSessionHasErrors('id_number');
        $this->assertSame(1, User::where('role', 'borrower')->count());
    }

    public function test_unverified_user_cannot_log_in(): void
    {
        Mail::fake();
        $this->register();
        $this->post('/logout');
        $this->session(['otp_user_id' => null]);

        $this->post('/login', ['email' => 'student123@gmail.com', 'password' => 'Password123'])->assertRedirect(route('otp.show'));
        $this->assertGuest();
    }

    public function test_unverified_session_cannot_reach_borrower_pages(): void
    {
        $user = User::create(['name' => 'U', 'email' => 'u@t.test', 'password' => 'Password123', 'role' => 'borrower', 'id_number' => 'X1', 'is_active' => true]);
        $this->actingAs($user)->get('/borrower')->assertRedirect(route('login'));
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();
        $user = User::create(['name' => 'U', 'email' => 'u@t.test', 'password' => 'Password123', 'role' => 'borrower', 'id_number' => 'X2', 'is_active' => true, 'email_verified_at' => now()]);

        $this->post('/forgot-password', ['email' => 'u@t.test'])->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
