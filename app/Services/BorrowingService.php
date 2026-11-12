<?php

namespace App\Services;

use App\Models\BorrowingRequest;
use App\Models\BorrowingTransaction;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All borrowing rules live here so controllers stay thin.
 *
 *  submit()  -> creates a PENDING request (stock is NOT touched)
 *  approve() -> locks equipment rows, re-checks stock, decrements available_quantity
 *  reject()  -> no stock change
 *  cancel()  -> borrower cancels own pending request, no stock change
 */
class BorrowingService
{
    public function submit(User $user, array $data): BorrowingRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $items = collect($data['items']);
            $equipment = Equipment::whereIn('id', $items->pluck('equipment_id'))->get()->keyBy('id');

            foreach ($items as $item) {
                $eq = $equipment[$item['equipment_id']];

                if ($eq->status !== 'active') {
                    throw ValidationException::withMessages(['items' => "{$eq->name} is not available for borrowing."]);
                }
                if ($eq->available_quantity < 1) {
                    throw ValidationException::withMessages(['items' => "{$eq->name} is out of stock."]);
                }
                if ((int) $item['quantity'] > $eq->available_quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$eq->available_quantity} unit(s) of {$eq->name} are available.",
                    ]);
                }
            }

            $request = BorrowingRequest::create([
                'user_id'              => $user->id,
                'borrow_date'          => $data['borrow_date'],
                'expected_return_date' => $data['expected_return_date'],
                'purpose'              => $data['purpose'],
                'status'               => 'pending',
            ]);
            $request->update(['request_no' => 'REQ-' . str_pad($request->id, 5, '0', STR_PAD_LEFT)]);

            foreach ($items as $item) {
                $request->items()->create([
                    'equipment_id' => $item['equipment_id'],
                    'quantity'     => (int) $item['quantity'],
                ]);
            }

            return $request;
        });
    }

    public function approve(BorrowingRequest $borrowingRequest, User $admin): BorrowingRequest
    {
        return DB::transaction(function () use ($borrowingRequest, $admin) {
            // Lock the request row so two admins cannot approve it twice
            $request = BorrowingRequest::lockForUpdate()->findOrFail($borrowingRequest->id);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'This request has already been processed.']);
            }

            $items = $request->items()->get();

            // Lock equipment rows (ordered by id to avoid deadlocks) and re-check stock
            $equipment = Equipment::whereIn('id', $items->pluck('equipment_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $eq = $equipment[$item->equipment_id] ?? null;
                if (!$eq) {
                    throw ValidationException::withMessages(['status' => "Cannot approve: {$item->display_name} was removed from the inventory."]);
                }
                if ($eq->status !== 'active' || $item->quantity > $eq->available_quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Cannot approve: only {$eq->available_quantity} unit(s) of {$eq->name} left (requested {$item->quantity}).",
                    ]);
                }
            }

            foreach ($items as $item) {
                $equipment[$item->equipment_id]->decrement('available_quantity', $item->quantity);
            }

            BorrowingTransaction::create([
                'borrowing_request_id' => $request->id,
                'approved_by'          => $admin->id,
                'approved_at'          => now(),
                'due_date'             => $request->expected_return_date,
                'status'               => 'active',
            ]);

            $request->update([
                'status'           => 'approved',
                'reviewed_by'      => $admin->id,
                'reviewed_at'      => now(),
                'rejection_reason' => null,
            ]);

            return $request;
        });
    }

    public function reject(BorrowingRequest $borrowingRequest, User $admin, string $reason): BorrowingRequest
    {
        return DB::transaction(function () use ($borrowingRequest, $admin, $reason) {
            $request = BorrowingRequest::lockForUpdate()->findOrFail($borrowingRequest->id);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'This request has already been processed.']);
            }

            $request->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'reviewed_by'      => $admin->id,
                'reviewed_at'      => now(),
            ]);

            return $request;
        });
    }

    public function cancel(BorrowingRequest $borrowingRequest, User $user): BorrowingRequest
    {
        return DB::transaction(function () use ($borrowingRequest, $user) {
            $request = BorrowingRequest::lockForUpdate()->findOrFail($borrowingRequest->id);

            if ($request->user_id !== $user->id) {
                abort(403);
            }
            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending requests can be cancelled.']);
            }

            $request->update(['status' => 'cancelled']);
            return $request;
        });
    }
}
