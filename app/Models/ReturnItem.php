<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $fillable = [
        'return_id', 'equipment_id', 'equipment_name', 'equipment_code', 'quantity_borrowed', 'quantity_returned_good',
        'quantity_damaged', 'quantity_missing', 'condition_on_return',
    ];

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

    public function returnRecord() { return $this->belongsTo(ReturnRecord::class, 'return_id'); }
    public function equipment()    { return $this->belongsTo(Equipment::class); }

    public function getDisplayNameAttribute(): string
    {
        return $this->equipment?->name ?? $this->equipment_name ?? 'Deleted equipment';
    }
}
