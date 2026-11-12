<?php

namespace Tests\Feature;

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

    public function test_registration_activates_and_signs_in_without_sending_a_verification_email(): void
    {
        Mail::fake();
        $this->register()->assertRedirect(route('dashboard'))->assertSessionHas('success');
        $user = User::where('email', 'student123@gmail.com')->first();
        $this->assertTrue($user->is_active);
        $this->assertSame('borrower', $user->role);
        $this->assertAuthenticatedAs($user);
        Mail::assertNothingSent();
    }

    public function test_newly_registered_account_can_log_in_again_without_verification(): void
    {
        $this->register()->assertRedirect(route('dashboard'));
        $user = User::where('email', 'student123@gmail.com')->first();
        $this->post('/logout');

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_verification_route_is_not_needed_for_registration(): void
    {
        $this->get('/verify-otp')->assertNotFound();
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
