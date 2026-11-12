<?php

namespace Database\Seeders;

use App\Models\BorrowingRequest;
use App\Models\BorrowingTransaction;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\ReturnRecord;
use App\Models\User;
use App\Services\BorrowingService;
use Illuminate\Database\Seeder;

/** Fictional demo data only. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Demo Administrator', 'email' => 'admin@demo.test', 'password' => 'Admin@12345',
            'role' => 'admin', 'id_number' => 'ADM-001', 'is_active' => true, 'email_verified_at' => now(),
        ]);

        $borrowers = collect([
            ['Maria Santos', 'borrower@demo.test', '2024-0001'],
            ['Juan Dela Cruz', 'juan@demo.test', '2024-0002'],
            ['Ana Reyes', 'ana@demo.test', '2024-0003'],
            ['Carlo Mendoza', 'carlo@demo.test', '2024-0004'],
        ])->map(fn ($b) => User::create([
            'name' => $b[0], 'email' => $b[1], 'password' => 'Borrower@12345', 'role' => 'borrower',
            'id_number' => $b[2], 'phone' => '0917000' . rand(1000, 9999), 'is_active' => true, 'email_verified_at' => now(),
        ]));

        $cats = collect(['Audio-Visual', 'Computing', 'Laboratory', 'Sports', 'Photography'])
            ->mapWithKeys(fn ($n) => [$n => Category::create(['name' => $n, 'description' => "$n equipment"])]);

        $catalog = [
            ['LCD Projector', 'Audio-Visual', 6, 'Portable projector with HDMI and VGA.'],
            ['Wireless Microphone Set', 'Audio-Visual', 8, 'Dual-channel wireless microphone set.'],
            ['Portable Speaker', 'Audio-Visual', 5, 'Bluetooth PA speaker with stand.'],
            ['Laptop (Core i5)', 'Computing', 10, 'Windows laptop with charger and bag.'],
            ['Tablet', 'Computing', 12, '10-inch tablet with stylus.'],
            ['Extension Cord (10m)', 'Computing', 15, 'Heavy-duty extension cord, 4 outlets.'],
            ['Digital Microscope', 'Laboratory', 4, 'USB digital microscope, 1000x.'],
            ['Basketball', 'Sports', 12, 'Size 7 indoor/outdoor basketball.'],
            ['Volleyball Net Set', 'Sports', 3, 'Net, poles, and carrying bag.'],
            ['DSLR Camera', 'Photography', 3, 'DSLR with 18-55mm lens and SD card.'],
            ['Tripod', 'Photography', 6, 'Adjustable aluminum tripod.'],
        ];

        $eq = [];
        foreach ($catalog as $i => [$name, $cat, $qty, $desc]) {
            $eq[$name] = Equipment::create([
                'equipment_code' => 'EQ-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'category_id' => $cats[$cat]->id, 'name' => $name, 'description' => $desc,
                'quantity' => $qty, 'available_quantity' => $qty, 'condition' => 'good', 'status' => 'active',
            ]);
        }

        $service = app(BorrowingService::class);

        // Pending requests (stock untouched)
        $service->submit($borrowers[0], [
            'borrow_date' => now()->addDays(2)->toDateString(), 'expected_return_date' => now()->addDays(4)->toDateString(),
            'purpose' => 'Class presentation for Science Fair', 'items' => [
                ['equipment_id' => $eq['LCD Projector']->id, 'quantity' => 1],
                ['equipment_id' => $eq['Extension Cord (10m)']->id, 'quantity' => 2],
            ],
        ]);
        $service->submit($borrowers[1], [
            'borrow_date' => now()->addDay()->toDateString(), 'expected_return_date' => now()->addDays(3)->toDateString(),
            'purpose' => 'Intramurals practice', 'items' => [['equipment_id' => $eq['Basketball']->id, 'quantity' => 4]],
        ]);

        // Approved via the real service (decrements stock)
        $approved = $service->submit($borrowers[2], [
            'borrow_date' => now()->toDateString(), 'expected_return_date' => now()->addDays(5)->toDateString(),
            'purpose' => 'Photo documentation of campus event', 'items' => [
                ['equipment_id' => $eq['DSLR Camera']->id, 'quantity' => 1],
                ['equipment_id' => $eq['Tripod']->id, 'quantity' => 2],
            ],
        ]);
        $service->approve($approved, $admin);

        // Rejected
        $rejected = $service->submit($borrowers[3], [
            'borrow_date' => now()->addDays(3)->toDateString(), 'expected_return_date' => now()->addDays(6)->toDateString(),
            'purpose' => 'Weekend event', 'items' => [['equipment_id' => $eq['Portable Speaker']->id, 'quantity' => 2]],
        ]);
        $service->reject($rejected, $admin, 'Speakers are reserved for the school assembly.');

        // Overdue: dates are in the past, so we insert directly and adjust stock by hand
        $overdue = $this->historic($borrowers[1], $admin, now()->subDays(10), now()->subDays(4), 'Laboratory activity', 'overdue',
            [[$eq['Digital Microscope'], 2], [$eq['Laptop (Core i5)'], 1]]);
        $overdue->transaction->update(['status' => 'overdue']);

        // Returned (history)
        $returned = $this->historic($borrowers[0], $admin, now()->subDays(20), now()->subDays(15), 'Seminar workshop', 'returned',
            [[$eq['Wireless Microphone Set'], 2]]);
        $returned->transaction->update(['status' => 'returned']);
        $record = ReturnRecord::create([
            'borrowing_transaction_id' => $returned->transaction->id,
            'actual_return_date' => now()->subDays(15)->toDateString(), 'notes' => 'Returned complete.', 'processed_by' => $admin->id,
        ]);
        foreach ($returned->items as $item) {
            $record->items()->create([
                'equipment_id' => $item->equipment_id, 'quantity_borrowed' => $item->quantity,
                'quantity_returned_good' => $item->quantity, 'condition_on_return' => 'good',
            ]);
            // Returned in good shape: nothing to adjust, stock never left the shelf in the seed
        }
    }

    /** Helper that builds an old (past-dated) request chain for demo history. */
    private function historic(User $user, User $admin, $borrow, $due, string $purpose, string $status, array $lines): BorrowingRequest
    {
        $request = BorrowingRequest::create([
            'user_id' => $user->id, 'borrow_date' => $borrow->toDateString(), 'expected_return_date' => $due->toDateString(),
            'purpose' => $purpose, 'status' => $status, 'reviewed_by' => $admin->id, 'reviewed_at' => $borrow,
        ]);
        $request->update(['request_no' => 'REQ-' . str_pad($request->id, 5, '0', STR_PAD_LEFT)]);

        foreach ($lines as [$equipment, $qty]) {
            $request->items()->create(['equipment_id' => $equipment->id, 'quantity' => $qty]);
            if ($status === 'overdue') {
                $equipment->decrement('available_quantity', $qty); // still out with the borrower
            }
        }

        BorrowingTransaction::create([
            'borrowing_request_id' => $request->id, 'approved_by' => $admin->id,
            'approved_at' => $borrow, 'due_date' => $due->toDateString(), 'status' => 'active',
        ]);

        return $request->load('transaction', 'items');
    }
}
