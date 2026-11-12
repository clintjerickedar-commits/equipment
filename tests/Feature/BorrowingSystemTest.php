<?php

namespace Tests\Feature;

use App\Models\BorrowingRequest;
use App\Models\BorrowingTransaction;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\ReturnRecord;
use App\Models\User;
use App\Services\BorrowingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingSystemTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@t.test', 'password' => 'Admin@12345', 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function borrower(string $email = 'b@t.test'): User
    {
        return User::create(['name' => 'Borrower', 'email' => $email, 'password' => 'Borrower@123', 'role' => 'borrower', 'id_number' => '1', 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function equipment(int $qty = 5, array $extra = []): Equipment
    {
        $cat = Category::firstOrCreate(['name' => 'Test']);
        return Equipment::create(array_merge([
            'equipment_code' => 'EQ-' . uniqid(), 'category_id' => $cat->id, 'name' => 'Projector', 'quantity' => $qty,
            'available_quantity' => $qty, 'condition' => 'good', 'status' => 'active',
        ], $extra));
    }

    private function payload(Equipment $eq, int $qty): array
    {
        return [
            'borrow_date' => now()->toDateString(), 'expected_return_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Class presentation', 'items' => [['equipment_id' => $eq->id, 'quantity' => $qty]],
        ];
    }

    // ---------- Authentication ----------
    public function test_login_success_and_invalid_credentials(): void
    {
        $this->borrower();
        $this->post('/login', ['email' => 'b@t.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => 'b@t.test', 'password' => 'Borrower@123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_unauthorized_access_is_blocked(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));                  // guest
        $this->actingAs($this->borrower())->get('/admin')->assertForbidden();       // borrower -> admin area
        $this->actingAs($this->admin())->get('/borrower')->assertForbidden();       // admin -> borrower area
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = $this->borrower();
        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => 'b@t.test', 'password' => 'Borrower@123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ---------- Equipment CRUD ----------
    public function test_admin_can_add_edit_and_delete_equipment(): void
    {
        $admin = $this->admin();
        $cat = Category::create(['name' => 'Audio']);

        $this->actingAs($admin)->post('/admin/equipment', [
            'name' => 'Speaker', 'category_id' => $cat->id, 'quantity' => 4, 'condition' => 'good', 'status' => 'active',
        ])->assertRedirect(route('admin.equipment.index'));
        $eq = Equipment::where('name', 'Speaker')->first();
        $this->assertSame(4, $eq->available_quantity);

        $this->actingAs($admin)->put("/admin/equipment/{$eq->id}", [
            'name' => 'Speaker Pro', 'category_id' => $cat->id, 'quantity' => 6, 'condition' => 'good', 'status' => 'active',
        ])->assertRedirect();
        $this->assertSame(6, $eq->fresh()->available_quantity);

        $this->actingAs($admin)->delete("/admin/equipment/{$eq->id}")->assertRedirect();
        $this->assertDatabaseMissing('equipment', ['id' => $eq->id]);
    }

    // ---------- Requests ----------
    public function test_borrower_can_submit_request_and_stock_is_not_reduced_while_pending(): void
    {
        $eq = $this->equipment(5);
        $this->actingAs($this->borrower())->post('/borrower/requests', $this->payload($eq, 2))->assertRedirect(route('borrower.requests.index'));

        $this->assertDatabaseHas('borrowing_requests', ['status' => 'pending']);
        $this->assertSame(5, $eq->fresh()->available_quantity);
    }

    public function test_cannot_request_more_than_available_stock(): void
    {
        $eq = $this->equipment(3);
        $this->actingAs($this->borrower())->post('/borrower/requests', $this->payload($eq, 4))->assertSessionHasErrors('items');
        $this->assertDatabaseCount('borrowing_requests', 0);
    }

    public function test_cannot_request_unavailable_equipment(): void
    {
        $out = $this->equipment(2, ['available_quantity' => 0]);
        $maint = $this->equipment(2, ['status' => 'maintenance']);
        $user = $this->borrower();
        $this->actingAs($user)->post('/borrower/requests', $this->payload($out, 1))->assertSessionHasErrors('items');
        $this->actingAs($user)->post('/borrower/requests', $this->payload($maint, 1))->assertSessionHasErrors('items');
    }

    public function test_borrower_can_cancel_only_own_pending_request(): void
    {
        $eq = $this->equipment();
        $owner = $this->borrower();
        $other = $this->borrower('other@t.test');
        $req = app(BorrowingService::class)->submit($owner, $this->payload($eq, 1));

        $this->actingAs($other)->patch("/borrower/requests/{$req->id}/cancel")->assertForbidden();
        $this->actingAs($owner)->patch("/borrower/requests/{$req->id}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $req->fresh()->status);
    }

    // ---------- Approve / reject ----------
    public function test_approval_reduces_stock_and_rejection_does_not(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(5);
        $service = app(BorrowingService::class);
        $a = $service->submit($this->borrower(), $this->payload($eq, 2));
        $b = $service->submit($this->borrower('c@t.test'), $this->payload($eq, 1));

        $this->actingAs($admin)->post("/admin/requests/{$a->id}/approve")->assertRedirect();
        $this->assertSame(3, $eq->fresh()->available_quantity);
        $this->assertSame('approved', $a->fresh()->status);
        $this->assertDatabaseHas('borrowing_transactions', ['borrowing_request_id' => $a->id, 'status' => 'active']);

        $this->actingAs($admin)->post("/admin/requests/{$b->id}/reject", ['rejection_reason' => 'Not available that day'])->assertRedirect();
        $this->assertSame('rejected', $b->fresh()->status);
        $this->assertSame(3, $eq->fresh()->available_quantity);
    }

    public function test_request_cannot_be_approved_twice(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(5);
        $req = app(BorrowingService::class)->submit($this->borrower(), $this->payload($eq, 2));

        $this->actingAs($admin)->post("/admin/requests/{$req->id}/approve");
        $this->actingAs($admin)->post("/admin/requests/{$req->id}/approve")->assertSessionHasErrors('status');
        $this->assertSame(3, $eq->fresh()->available_quantity); // reduced only once
    }

    public function test_approval_fails_if_stock_was_taken_by_another_approval(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(3);
        $service = app(BorrowingService::class);
        $first = $service->submit($this->borrower(), $this->payload($eq, 2));
        $second = $service->submit($this->borrower('c@t.test'), $this->payload($eq, 2)); // both valid at submit time

        $service->approve($first, $admin);
        $this->actingAs($admin)->post("/admin/requests/{$second->id}/approve")->assertSessionHasErrors('status');
        $this->assertSame(1, $eq->fresh()->available_quantity);
        $this->assertSame('pending', $second->fresh()->status);
    }

    // ---------- Returns ----------
    private function approvedTransaction(Equipment $eq, int $qty): BorrowingTransaction
    {
        $admin = User::where('role', 'admin')->first() ?? $this->admin();
        $req = app(BorrowingService::class)->submit($this->borrower('r' . uniqid() . '@t.test'), $this->payload($eq, $qty));
        app(BorrowingService::class)->approve($req, $admin);
        return $req->fresh()->transaction;
    }

    public function test_return_restores_stock_and_duplicate_return_is_blocked(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(5);
        $tx = $this->approvedTransaction($eq, 3);
        $this->assertSame(2, $eq->fresh()->available_quantity);

        $data = ['actual_return_date' => now()->toDateString(), 'items' => [$eq->id => ['good' => 3, 'damaged' => 0, 'missing' => 0, 'condition' => 'good']]];
        $this->actingAs($admin)->post("/admin/returns/{$tx->id}", $data)->assertRedirect(route('admin.returns.index'));

        $this->assertSame(5, $eq->fresh()->available_quantity);
        $this->assertSame('returned', $tx->request->fresh()->status);

        // second attempt must not change stock or create a second record
        $this->actingAs($admin)->post("/admin/returns/{$tx->id}", $data)->assertSessionHasErrors('items');
        $this->assertSame(5, $eq->fresh()->available_quantity);
        $this->assertSame(1, ReturnRecord::count());
    }

    public function test_damaged_and_missing_units_are_removed_from_stock(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(5);
        $tx = $this->approvedTransaction($eq, 4);

        $this->actingAs($admin)->post("/admin/returns/{$tx->id}", [
            'actual_return_date' => now()->toDateString(),
            'items' => [$eq->id => ['good' => 2, 'damaged' => 1, 'missing' => 1, 'condition' => 'damaged']],
        ])->assertRedirect();

        $eq->refresh();
        $this->assertSame(3, $eq->quantity);            // 5 - 1 damaged - 1 missing
        $this->assertSame(3, $eq->available_quantity);  // 1 on shelf + 2 returned good
        $this->assertSame('needs_repair', $eq->condition);
    }

    public function test_return_quantities_must_add_up_to_borrowed_amount(): void
    {
        $eq = $this->equipment(5);
        $tx = $this->approvedTransaction($eq, 3);

        $this->actingAs(User::where('role', 'admin')->first())->post("/admin/returns/{$tx->id}", [
            'actual_return_date' => now()->toDateString(),
            'items' => [$eq->id => ['good' => 1, 'damaged' => 0, 'missing' => 0, 'condition' => 'good']],
        ])->assertSessionHasErrors('items');
        $this->assertSame(2, $eq->fresh()->available_quantity);
        $this->assertDatabaseCount('returns', 0);
    }

    // ---------- Overdue ----------
    public function test_overdue_detection_and_scheduled_command(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment(5);
        $req = BorrowingRequest::create(['user_id' => $this->borrower()->id, 'borrow_date' => now()->subDays(8), 'expected_return_date' => now()->subDays(2),
            'purpose' => 'Late item', 'status' => 'approved']);
        $req->items()->create(['equipment_id' => $eq->id, 'quantity' => 1]);
        BorrowingTransaction::create(['borrowing_request_id' => $req->id, 'approved_by' => $admin->id, 'approved_at' => now()->subDays(8), 'due_date' => now()->subDays(2), 'status' => 'active']);

        // Detected immediately, even before the scheduler runs
        $this->assertSame(1, BorrowingRequest::late()->count());
        $this->assertSame('overdue', $req->fresh()->display_status);

        $this->artisan('borrowing:mark-overdue')->assertSuccessful();
        $this->assertSame('overdue', $req->fresh()->status);
    }

    // ---------- Search / filter ----------
    public function test_equipment_search_and_filter(): void
    {
        $admin = $this->admin();
        $this->equipment(2, ['name' => 'Laser Pointer']);
        $this->equipment(2, ['name' => 'Basketball']);

        $this->actingAs($admin)->get('/admin/equipment?q=Laser')->assertSee('Laser Pointer')->assertDontSee('Basketball');
        $this->actingAs($admin)->get('/admin/equipment?status=retired')->assertDontSee('Laser Pointer');
    }

    public function test_request_status_filter(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment();
        $service = app(BorrowingService::class);
        $pending = $service->submit($this->borrower(), $this->payload($eq, 1));
        $rejected = $service->submit($this->borrower('x@t.test'), $this->payload($eq, 1));
        $service->reject($rejected, $admin, 'No stock planned');

        $this->actingAs($admin)->get('/admin/requests?status=pending')->assertSee($pending->request_no)->assertDontSee($rejected->request_no);
    }
}
