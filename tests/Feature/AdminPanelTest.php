<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $email = 'admin@t.test'): User
    {
        return User::create(['name' => 'Admin', 'email' => $email, 'password' => 'Admin@12345', 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function borrower(): User
    {
        return User::create(['name' => 'Borrower', 'email' => 'b@t.test', 'password' => 'Borrower@123', 'role' => 'borrower', 'id_number' => 'B1', 'is_active' => true, 'email_verified_at' => now()]);
    }

    public function test_admin_login_page_loads_and_admin_can_sign_in(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Administrator sign in');
        $this->admin();
        $this->post('/admin/login', ['email' => 'admin@t.test', 'password' => 'Admin@12345'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_borrower_credentials_are_rejected_on_admin_login(): void
    {
        $this->borrower();
        $this->post('/admin/login', ['email' => 'b@t.test', 'password' => 'Borrower@123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_admin_password_and_deactivated_admin_are_rejected(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => 'admin@t.test', 'password' => 'nope'])->assertSessionHasErrors('email');
        $admin->update(['is_active' => false]);
        $this->post('/admin/login', ['email' => 'admin@t.test', 'password' => 'Admin@12345'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guests_on_admin_pages_are_sent_to_admin_login_and_admin_logout_returns_there(): void
    {
        $this->get('/admin/equipment')->assertRedirect(route('admin.login'));
        $this->actingAs($this->admin())->post('/logout')->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_edit_and_delete_accounts_but_not_deactivate_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Second Admin', 'email' => 'second@t.test', 'role' => 'admin', 'password' => 'Password123', 'password_confirmation' => 'Password123',
        ])->assertRedirect(route('admin.users.index'));

        $second = User::where('email', 'second@t.test')->first();
        $this->assertTrue($second->isAdmin());
        $this->assertNotNull($second->email_verified_at);

        $this->actingAs($admin)->put("/admin/users/{$second->id}", ['name' => 'Renamed', 'email' => 'second@t.test', 'role' => 'admin'])->assertRedirect();
        $this->assertSame('Renamed', $second->fresh()->name);

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}/toggle")->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->actingAs($admin)->delete("/admin/users/{$second->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $second->id]);
    }

    public function test_borrower_cannot_open_user_management_or_settings(): void
    {
        $this->actingAs($this->borrower());
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
    }

    public function test_borrowing_limits_from_settings_are_enforced(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/settings', ['max_borrow_days' => 3, 'max_quantity_per_item' => 2])->assertRedirect();
        $this->assertSame('3', Setting::get('max_borrow_days'));

        $cat = Category::create(['name' => 'T']);
        $eq = Equipment::create(['equipment_code' => 'EQ-1', 'category_id' => $cat->id, 'name' => 'Projector', 'quantity' => 10, 'available_quantity' => 10, 'condition' => 'good', 'status' => 'active']);
        $borrower = $this->borrower();
        $base = ['borrow_date' => now()->toDateString(), 'purpose' => 'Class presentation'];

        $this->actingAs($borrower)->post('/borrower/requests', $base + ['expected_return_date' => now()->addDays(5)->toDateString(), 'items' => [['equipment_id' => $eq->id, 'quantity' => 1]]])
            ->assertSessionHasErrors('expected_return_date');
        $this->actingAs($borrower)->post('/borrower/requests', $base + ['expected_return_date' => now()->addDays(2)->toDateString(), 'items' => [['equipment_id' => $eq->id, 'quantity' => 3]]])
            ->assertSessionHasErrors('items.0.quantity');
        $this->actingAs($borrower)->post('/borrower/requests', $base + ['expected_return_date' => now()->addDays(2)->toDateString(), 'items' => [['equipment_id' => $eq->id, 'quantity' => 2]]])
            ->assertRedirect(route('borrower.requests.index'));
    }
}
