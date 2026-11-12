<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingRequest extends Model
{
    protected $fillable = [
        'request_no', 'user_id', 'borrow_date', 'expected_return_date', 'purpose',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date'          => 'date',
            'expected_return_date' => 'date',
            'reviewed_at'          => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'rejected', 'returned', 'overdue', 'cancelled'];

    public function user()        { return $this->belongsTo(User::class); }
    public function reviewer()    { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function items()       { return $this->hasMany(BorrowingRequestItem::class); }
    public function transaction() { return $this->hasOne(BorrowingTransaction::class); }

    /** Still out with the borrower (approved or already flagged overdue). */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['approved', 'overdue']);
    }

    /** Outstanding AND past the expected return date (works even if the scheduler is off). */
    public function scopeLate($query)
    {
        return $query->outstanding()->whereDate('expected_return_date', '<', now()->toDateString());
    }

    public function getIsLateAttribute(): bool
    {
        return in_array($this->status, ['approved', 'overdue'])
            && $this->expected_return_date->lt(now()->startOfDay());
    }

    /** Status shown in the UI: approved requests past their deadline display as overdue. */
    public function getDisplayStatusAttribute(): string
    {
        return $this->is_late ? 'overdue' : $this->status;
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getActualReturnDateAttribute()
    {
        return $this->transaction?->returnRecord?->actual_return_date;
    }
}
