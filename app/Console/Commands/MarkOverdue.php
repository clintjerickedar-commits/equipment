<?php

namespace App\Console\Commands;

use App\Models\BorrowingRequest;
use App\Models\BorrowingTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkOverdue extends Command
{
    protected $signature = 'borrowing:mark-overdue';
    protected $description = 'Flag approved borrowings that are past their expected return date as overdue';

    public function handle(): int
    {
        $count = 0;

        DB::transaction(function () use (&$count) {
            $transactions = BorrowingTransaction::where('status', 'active')
                ->whereDate('due_date', '<', now()->toDateString())->get();

            foreach ($transactions as $tx) {
                $tx->update(['status' => 'overdue']);
                BorrowingRequest::where('id', $tx->borrowing_request_id)->where('status', 'approved')->update(['status' => 'overdue']);
                $count++;
            }
        });

        $this->info("{$count} transaction(s) marked overdue.");
        return self::SUCCESS;
    }
}
