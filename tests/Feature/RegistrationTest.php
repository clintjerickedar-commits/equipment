<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email = 'student123@gmail.com', string $id = '2024-9999')
    {
        return $this->post('/register', [
            'name' => 'Test Student', 'email' => $email, 'id_number' => $id, 'phone' => '0917',
            'password' => 'Password123', 'password_confirmation' => 'Password123',
        ]);
    }

    public function test_registration_creates_and_logs_in_account_without_email_verification(): void
    {
        Mail::fake();
        $this->register()->assertRedirect(route('dashboard'));
        $user = User::where('email', 'student123@gmail.com')->first();
        $this->assertSame('borrower', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->get('/verify-otp')->assertNotFound();
        Mail::assertNothingSent();
    }

    public function test_new_account_can_log_in_without_email_verification_step(): void
    {
        $this->register();
        $this->post('/logout')->assertRedirect(route('home'));
        $this->post('/login', ['email' => 'student123@gmail.com', 'password' => 'Password123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_duplicate_email_and_id_number_are_rejected(): void
    {
        $this->register('dup@gmail.com', 'ID-1')->assertRedirect(route('dashboard'));
        $this->post('/logout');

        $this->register('dup@gmail.com', 'ID-2')->assertSessionHasErrors('email');
        $this->register('other@gmail.com', 'ID-1')->assertSessionHasErrors('id_number');
        $this->assertSame(1, User::where('role', 'borrower')->count());
    }

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();
        $user = User::create(['name' => 'U', 'email' => 'u@t.test', 'password' => 'Password123', 'role' => 'borrower', 'id_number' => 'X2', 'is_active' => true, 'email_verified_at' => now()]);

        $this->post('/forgot-password', ['email' => 'u@t.test'])->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
