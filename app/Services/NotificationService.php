<?php

namespace App\Services;

use App\Models\BorrowingRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /** @return array{items: Collection, count: int} */
    public function forUser(?User $user): array
    {
        $items = collect();

        if (!$user) {
            return ['items' => $items, 'count' => 0];
        }

        if ($user->isAdmin()) {
            foreach (BorrowingRequest::with('user')->where('status', 'pending')->latest()->take(4)->get() as $request) {
                $items->push([
                    'key' => 'admin:pending:' . $request->id,
                    'icon' => 'inbox',
                    'tone' => 'amber',
                    'title' => $request->user->name . ' sent a request',
                    'text' => $request->request_no . ' is waiting for review',
                    'url' => route('admin.requests.show', $request),
                    'time' => $request->created_at,
                ]);
            }

            foreach (BorrowingRequest::with('user')->late()->orderBy('expected_return_date')->take(3)->get() as $request) {
                $items->push([
                    'key' => 'admin:overdue:' . $request->id,
                    'icon' => 'alarm',
                    'tone' => 'red',
                    'title' => $request->user->name . ' is overdue',
                    'text' => $request->request_no . ' was due ' . $request->expected_return_date->format('M j'),
                    'url' => route('admin.requests.show', $request),
                    'time' => $request->expected_return_date,
                ]);
            }
        } else {
            $reviewed = BorrowingRequest::where('user_id', $user->id)->whereIn('status', ['approved', 'rejected'])
                ->where('reviewed_at', '>=', now()->subDays(7))->latest('reviewed_at')->take(4)->get();

            foreach ($reviewed as $request) {
                $approved = $request->status === 'approved';
                $items->push([
                    'key' => 'borrower:reviewed:' . $request->id . ':' . $request->status,
                    'icon' => $approved ? 'check2-circle' : 'x-circle',
                    'tone' => $approved ? 'green' : 'red',
                    'title' => $approved ? 'Request approved' : 'Request rejected',
                    'text' => $request->request_no,
                    'url' => route('borrower.requests.show', $request),
                    'time' => $request->reviewed_at,
                ]);
            }

            $due = BorrowingRequest::where('user_id', $user->id)->outstanding()
                ->whereDate('expected_return_date', '<=', now()->addDays(3)->toDateString())
                ->orderBy('expected_return_date')->take(3)->get();

            foreach ($due as $request) {
                $late = $request->is_late;
                $items->push([
                    'key' => 'borrower:due:' . $request->id . ':' . $request->expected_return_date->toDateString(),
                    'icon' => 'alarm',
                    'tone' => $late ? 'red' : 'amber',
                    'title' => $late ? 'Return is overdue' : 'Return due soon',
                    'text' => $request->request_no . ' · due ' . $request->expected_return_date->format('M j'),
                    'url' => route('borrower.requests.show', $request),
                    'time' => $request->expected_return_date,
                ]);
            }
        }

        $keys = $items->pluck('key');
        $readKeys = DB::table('notification_reads')
            ->where('user_id', $user->id)
            ->whereIn('notification_key', $keys)
            ->pluck('notification_key')
            ->all();

        return [
            'items' => $items,
            'count' => $items->whereNotIn('key', $readKeys)->count(),
        ];
    }

    /** @param string[] $keys */
    public function markRead(User $user, array $keys): void
    {
        $now = now();
        $records = collect($keys)->unique()->map(fn ($key) => [
            'user_id' => $user->id,
            'notification_key' => $key,
            'read_at' => $now,
        ])->all();

        if ($records) {
            DB::table('notification_reads')->upsert(
                $records,
                ['user_id', 'notification_key'],
                ['read_at']
            );
        }
    }
}
