<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingTransaction extends Model
{
    protected $fillable = ['borrowing_request_id', 'approved_by', 'approved_at', 'due_date', 'status'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'due_date' => 'date'];
    }

    public function request()      { return $this->belongsTo(BorrowingRequest::class, 'borrowing_request_id'); }
    public function approver()     { return $this->belongsTo(User::class, 'approved_by'); }
    public function returnRecord() { return $this->hasOne(ReturnRecord::class, 'borrowing_transaction_id'); }
}
