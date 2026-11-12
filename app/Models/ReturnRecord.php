<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRecord extends Model
{
    protected $table = 'returns';

    protected $fillable = ['borrowing_transaction_id', 'actual_return_date', 'notes', 'processed_by'];

    protected function casts(): array
    {
        return ['actual_return_date' => 'date'];
    }

    public function transaction() { return $this->belongsTo(BorrowingTransaction::class, 'borrowing_transaction_id'); }
    public function items()       { return $this->hasMany(ReturnItem::class, 'return_id'); }
    public function processor()   { return $this->belongsTo(User::class, 'processed_by'); }
}
