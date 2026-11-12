<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingRequestItem extends Model
{
    protected $fillable = ['borrowing_request_id', 'equipment_id', 'equipment_name', 'equipment_code', 'quantity'];

    /** Every new item remembers the equipment name and code, so history stays readable after the equipment is deleted. */
    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if ($item->equipment_id && (!$item->equipment_name || !$item->equipment_code)) {
                $equipment = Equipment::find($item->equipment_id);
                if ($equipment) {
                    $item->equipment_name = $item->equipment_name ?: $equipment->name;
                    $item->equipment_code = $item->equipment_code ?: $equipment->equipment_code;
                }
            }
        });
    }

    public function request()   { return $this->belongsTo(BorrowingRequest::class, 'borrowing_request_id'); }
    public function equipment() { return $this->belongsTo(Equipment::class); }

    /** Live name while the equipment exists, saved snapshot after it was deleted. */
    public function getDisplayNameAttribute(): string
    {
        return $this->equipment?->name ?? $this->equipment_name ?? 'Deleted equipment';
    }

    public function getDisplayCodeAttribute(): ?string
    {
        return $this->equipment?->equipment_code ?? $this->equipment_code;
    }
}
