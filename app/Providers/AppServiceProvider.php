<?php

namespace App\Providers;

use App\Models\BorrowingRequest;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Data for the notification bell in the top bar (built from existing tables, nothing new is stored)
        View::composer('layouts.app', function ($view) {
            $view->with('notifications', $this->notificationsFor(auth()->user()));
        });
    }

    /** @return array{items: \Illuminate\Support\Collection, count: int} */
    private function notificationsFor($user): array
    {
        $items = collect();
        $count = 0;

        if (!$user) {
            return compact('items', 'count');
        }

        try {
            if ($user->isAdmin()) {
                $pendingTotal = BorrowingRequest::where('status', 'pending')->count();
                $lateTotal    = BorrowingRequest::late()->count();
                $count        = $pendingTotal + $lateTotal;

                foreach (BorrowingRequest::with('user')->where('status', 'pending')->latest()->take(4)->get() as $r) {
                    $items->push(['icon' => 'inbox', 'tone' => 'amber', 'title' => $r->user->name . ' sent a request',
                        'text' => $r->request_no . ' is waiting for review', 'url' => route('admin.requests.show', $r), 'time' => $r->created_at]);
                }
                foreach (BorrowingRequest::with('user')->late()->orderBy('expected_return_date')->take(3)->get() as $r) {
                    $items->push(['icon' => 'alarm', 'tone' => 'red', 'title' => $r->user->name . ' is overdue',
                        'text' => $r->request_no . ' was due ' . $r->expected_return_date->format('M j'), 'url' => route('admin.requests.show', $r), 'time' => $r->expected_return_date]);
                }
            } else {
                $reviewed = BorrowingRequest::where('user_id', $user->id)->whereIn('status', ['approved', 'rejected'])
                    ->where('reviewed_at', '>=', now()->subDays(7))->latest('reviewed_at')->take(4)->get();
                foreach ($reviewed as $r) {
                    $ok = $r->status === 'approved';
                    $items->push(['icon' => $ok ? 'check2-circle' : 'x-circle', 'tone' => $ok ? 'green' : 'red',
                        'title' => $ok ? 'Request approved' : 'Request rejected', 'text' => $r->request_no,
                        'url' => route('borrower.requests.show', $r), 'time' => $r->reviewed_at]);
                }

                $due = BorrowingRequest::where('user_id', $user->id)->outstanding()
                    ->whereDate('expected_return_date', '<=', now()->addDays(3)->toDateString())->orderBy('expected_return_date')->take(3)->get();
                foreach ($due as $r) {
                    $late = $r->is_late;
                    $items->push(['icon' => 'alarm', 'tone' => $late ? 'red' : 'amber', 'title' => $late ? 'Return is overdue' : 'Return due soon',
                        'text' => $r->request_no . ' · due ' . $r->expected_return_date->format('M j'),
                        'url' => route('borrower.requests.show', $r), 'time' => $r->expected_return_date]);
                }
                $count = $items->count();
            }
        } catch (\Throwable $e) {
            report($e); // never break a page because of the bell
        }

        return compact('items', 'count');
    }
}
