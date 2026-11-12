<?php

namespace Tests\Feature;

use App\Models\BorrowingRequest;
use App\Models\BorrowingTransaction;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EquipmentDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::firstOrCreate(['email' => 'a@t.test'], ['name' => 'Admin One', 'password' => 'Admin@12345', 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function borrower(): User
    {
        return User::firstOrCreate(['email' => 'b@t.test'], ['name' => 'Borrower', 'password' => 'Borrower@123', 'role' => 'borrower', 'id_number' => 'B1', 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function equipment(string $name = 'Tripod'): Equipment
    {
        $cat = Category::firstOrCreate(['name' => 'Photo']);
        return Equipment::create(['equipment_code' => 'EQ-' . uniqid(), 'category_id' => $cat->id, 'name' => $name, 'quantity' => 3, 'available_quantity' => 3, 'condition' => 'good', 'status' => 'active']);
    }

    private function request(Equipment $eq, string $status): BorrowingRequest
    {
        $req = BorrowingRequest::create(['user_id' => $this->borrower()->id, 'borrow_date' => now()->subDays(5), 'expected_return_date' => now()->addDays(2), 'purpose' => 'Test', 'status' => $status]);
        $req->update(['request_no' => 'REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)]);
        $req->items()->create(['equipment_id' => $eq->id, 'quantity' => 1]);
        return $req;
    }

    public function test_the_database_allows_history_to_outlive_the_equipment(): void
    {
        foreach (['borrowing_request_items', 'return_items'] as $table) {
            $this->assertTrue((bool) collect(Schema::getColumns($table))->firstWhere('name', 'equipment_id')['nullable'], "$table.equipment_id must be nullable");
            $this->assertTrue(Schema::hasColumns($table, ['equipment_name', 'equipment_code']), "$table needs the name/code snapshot");
        }
    }

    public function test_delete_button_sends_the_right_item_and_shows_the_confirmation(): void
    {
        $eq = $this->equipment('Studio Tripod');
        $this->actingAs($this->admin())->get('/admin/equipment')->assertOk()
            ->assertSee('data-confirm="Are you sure you want to delete this equipment?"', false)
            ->assertSee('data-confirm-name="Studio Tripod (' . $eq->equipment_code . ')"', false)
            ->assertSee('data-confirm-button="Delete"', false)
            ->assertSee('action="' . route('admin.equipment.destroy', $eq) . '"', false)   // this item's own id
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_equipment_without_history_is_deleted_for_real(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment('Old Tripod');
        $other = $this->equipment('Keep Me');

        $this->actingAs($admin)->delete("/admin/equipment/{$eq->id}")
            ->assertRedirect(route('admin.equipment.index'))->assertSessionHas('success', 'Equipment deleted successfully!');

        $this->assertDatabaseMissing('equipment', ['id' => $eq->id]);
        $this->assertDatabaseHas('equipment', ['id' => $other->id]);                       // only the selected item
        $this->actingAs($admin)->get('/admin/equipment')->assertOk()->assertDontSee('Old Tripod')->assertSee('Keep Me');
    }

    public function test_equipment_with_borrowing_history_can_be_deleted_and_the_history_is_preserved(): void
    {
        $admin = $this->admin();
        $eq = $this->equipment('Projector');
        $code = $eq->equipment_code;

        $req = $this->request($eq, 'returned');
        $tx  = BorrowingTransaction::create(['borrowing_request_id' => $req->id, 'approved_by' => $admin->id, 'approved_at' => now()->subDays(5), 'due_date' => now()->subDay(), 'status' => 'returned']);
        $ret = ReturnRecord::create(['borrowing_transaction_id' => $tx->id, 'actual_return_date' => now()->subDay(), 'processed_by' => $admin->id]);
        $ret->items()->create(['equipment_id' => $eq->id, 'quantity_borrowed' => 1, 'quantity_returned_good' => 1, 'condition_on_return' => 'good']);

        $this->actingAs($admin)->delete("/admin/equipment/{$eq->id}")->assertRedirect()->assertSessionHas('success', 'Equipment deleted successfully!');

        // the equipment is gone ...
        $this->assertDatabaseMissing('equipment', ['id' => $eq->id]);
        // ... every history row is still there, pointing at nothing but remembering the name and code
        $this->assertDatabaseHas('borrowing_requests', ['id' => $req->id]);
        $this->assertDatabaseHas('borrowing_transactions', ['id' => $tx->id]);
        $this->assertDatabaseHas('returns', ['id' => $ret->id]);
        $this->assertDatabaseHas('borrowing_request_items', ['borrowing_request_id' => $req->id, 'equipment_id' => null, 'equipment_name' => 'Projector', 'equipment_code' => $code, 'quantity' => 1]);
        $this->assertDatabaseHas('return_items', ['return_id' => $ret->id, 'equipment_id' => null, 'equipment_name' => 'Projector', 'equipment_code' => $code]);
        $this->assertDatabaseHas('users', ['id' => $req->user_id]);

        // ... and every page that shows history still renders it
        $this->actingAs($admin)->get("/admin/requests/{$req->id}")->assertOk()->assertSee('Projector')->assertSee($code);
        $this->actingAs($admin)->get('/admin/history')->assertOk()->assertSee('Projector');
        $this->actingAs($admin)->get('/admin/history?q=Projector')->assertOk()->assertSee($req->request_no);   // searchable by the saved name
        $this->actingAs($admin)->get('/admin/reports?type=returned')->assertOk()->assertSee('Projector');
        $this->actingAs($this->borrower())->get('/borrower/history')->assertOk()->assertSee('Projector');
        $this->actingAs($this->borrower())->get("/borrower/requests/{$req->id}")->assertOk()->assertSee('Projector');
    }

    public function test_equipment_that_is_currently_borrowed_must_be_returned_first(): void
    {
        $eq = $this->equipment('Camera');
        $eq->update(['available_quantity' => 2]);
        $this->request($eq, 'approved');

        $this->actingAs($this->admin())->delete("/admin/equipment/{$eq->id}")->assertRedirect()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'currently borrowed') && str_contains($m, 'Camera'));

        $this->assertDatabaseHas('equipment', ['id' => $eq->id]);
    }

    public function test_pending_requests_for_deleted_equipment_are_rejected_with_a_reason(): void
    {
        $eq = $this->equipment('Speaker');
        $pending = $this->request($eq, 'pending');

        $this->actingAs($this->admin())->delete("/admin/equipment/{$eq->id}")->assertRedirect()
            ->assertSessionHas('success', fn ($m) => str_starts_with($m, 'Equipment deleted successfully!') && str_contains($m, '1 pending request(s)'));

        $pending->refresh();
        $this->assertSame('rejected', $pending->status);
        $this->assertStringContainsString('Speaker', $pending->rejection_reason);
        $this->assertDatabaseHas('borrowing_request_items', ['borrowing_request_id' => $pending->id, 'equipment_id' => null, 'equipment_name' => 'Speaker']);
    }

    public function test_list_and_pagination_are_correct_after_deleting(): void
    {
        $admin = $this->admin();
        foreach (range(1, 11) as $n) {
            $this->equipment('Item ' . str_pad($n, 2, '0', STR_PAD_LEFT));
        }
        $this->actingAs($admin)->get('/admin/equipment?page=2')->assertOk()->assertSee('Item 11');

        // the only item on page 2 is deleted: the admin lands on the (new) last page, not on an empty page
        $last = Equipment::where('name', 'Item 11')->first();
        $this->actingAs($admin)->delete("/admin/equipment/{$last->id}", ['page' => 2])->assertRedirect(route('admin.equipment.index'));
        $this->actingAs($admin)->get('/admin/equipment')->assertOk()->assertDontSee('Item 11')->assertSee('Item 10');

        // search filter is kept after a delete
        $first = Equipment::where('name', 'Item 01')->first();
        $this->actingAs($admin)->delete("/admin/equipment/{$first->id}", ['q' => 'Item', 'page' => 1])->assertRedirect(route('admin.equipment.index', ['q' => 'Item']));
    }

    public function test_demo_data_end_to_end(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@demo.test')->first();

        // DSLR Camera is out with Ana right now: blocked, still in the list
        $camera = Equipment::where('name', 'DSLR Camera')->first();
        $this->actingAs($admin)->delete("/admin/equipment/{$camera->id}", ['page' => 1])->assertSessionHas('error');
        $this->assertDatabaseHas('equipment', ['id' => $camera->id]);

        // the microphones only appear in an old, returned borrowing: deletable, history survives
        $mic = Equipment::where('name', 'Wireless Microphone Set')->first();
        $this->actingAs($admin)->delete("/admin/equipment/{$mic->id}", ['page' => 1])->assertSessionHas('success', 'Equipment deleted successfully!');
        $this->assertDatabaseMissing('equipment', ['id' => $mic->id]);
        $this->actingAs($admin)->get('/admin/history')->assertOk()->assertSee('Wireless Microphone Set');
        $this->actingAs($admin)->get('/admin')->assertOk();                      // dashboard still renders
        $this->actingAs($admin)->get('/admin/returns')->assertOk();
        $this->actingAs($admin)->get('/admin/reports?type=inventory')->assertOk()->assertDontSee('Wireless Microphone Set');
    }

    public function test_borrowers_cannot_delete_equipment(): void
    {
        $eq = $this->equipment();
        $this->actingAs($this->borrower())->delete("/admin/equipment/{$eq->id}")->assertForbidden();
        $this->assertDatabaseHas('equipment', ['id' => $eq->id]);
    }
}
