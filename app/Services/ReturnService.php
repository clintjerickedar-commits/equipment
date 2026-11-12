<?php

namespace App\Services;

use App\Models\BorrowingTransaction;
use App\Models\Equipment;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock rules when equipment comes back:
 *   available_quantity += units returned in good/fair shape
 *   quantity           -= units damaged or missing (no longer usable)
 * A UNIQUE key on returns.borrowing_transaction_id plus a row lock prevents duplicate returns.
 */
class ReturnService
{
    /**
     * @param array $data ['actual_return_date', 'notes', 'items' => [equipment_id => [good, damaged, missing, condition]]]
     */
    public function process(BorrowingTransaction $transaction, array $data, User $admin): ReturnRecord
    {
        return DB::transaction(function () use ($transaction, $data, $admin) {
            $tx = BorrowingTransaction::lockForUpdate()->findOrFail($transaction->id);

            if ($tx->status === 'returned' || ReturnRecord::where('borrowing_transaction_id', $tx->id)->exists()) {
                throw ValidationException::withMessages(['items' => 'This transaction has already been returned.']);
            }

            $request = $tx->request()->with('items')->first();

            // Validate each line: good + damaged + missing must equal the quantity borrowed
            foreach ($request->items as $item) {
                $line = $data['items'][$item->equipment_id] ?? null;
                if (!$line) {
                    throw ValidationException::withMessages(['items' => 'Missing return details for an item.']);
                }
                $sum = (int) $line['good'] + (int) $line['damaged'] + (int) $line['missing'];
                if ($sum !== $item->quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Quantities for {$item->display_name} must add up to {$item->quantity} (you entered {$sum}).",
                    ]);
                }
            }

            $return = ReturnRecord::create([
                'borrowing_transaction_id' => $tx->id,
                'actual_return_date'       => $data['actual_return_date'],
                'notes'                    => $data['notes'] ?? null,
                'processed_by'             => $admin->id,
            ]);

            $equipment = Equipment::whereIn('id', $request->items->pluck('equipment_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($request->items as $item) {
                $line = $data['items'][$item->equipment_id];
                $good = (int) $line['good'];
                $damaged = (int) $line['damaged'];
                $missing = (int) $line['missing'];

                $return->items()->create([
                    'equipment_id'           => $item->equipment_id,
                    'quantity_borrowed'      => $item->quantity,
                    'quantity_returned_good' => $good,
                    'quantity_damaged'       => $damaged,
                    'quantity_missing'       => $missing,
                    'condition_on_return'    => $line['condition'],
                ]);

                $eq = $equipment[$item->equipment_id] ?? null;
                if (!$eq) {
                    continue; // equipment no longer exists: the return is still recorded, there is no stock to update
                }
                $eq->available_quantity += $good;
                $eq->quantity           -= ($damaged + $missing);

                // Reflect the returned condition on the equipment record
                if ($damaged > 0 || $line['condition'] === 'damaged') {
                    $eq->condition = 'needs_repair';
                } elseif ($line['condition'] === 'fair' && $eq->condition === 'good') {
                    $eq->condition = 'fair';
                }
                $eq->save();
            }

            $tx->update(['status' => 'returned']);
            $request->update(['status' => 'returned']);

            return $return;
        });
    }
}
