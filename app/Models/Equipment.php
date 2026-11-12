<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'equipment_code', 'category_id', 'name', 'description', 'quantity',
        'available_quantity', 'image_path', 'condition', 'status',
    ];

    public const CONDITIONS = ['good' => 'Good', 'fair' => 'Fair', 'needs_repair' => 'Needs repair'];
    public const STATUSES   = ['active' => 'Active', 'maintenance' => 'Under maintenance', 'retired' => 'Retired'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestItems()
    {
        return $this->hasMany(BorrowingRequestItem::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class);
    }

    /** Search + category + status filters, shared by the list page and the delete redirect. */
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('equipment_code', 'like', "%{$s}%")))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category_id', $c))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s));
    }

    /** Units that are currently out with borrowers. */
    public function getBorrowedQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->available_quantity);
    }

    public function getImageUrlAttribute(): ?string
    {
        // asset() follows the host you opened the site with, so images work on localhost, 127.0.0.1 or a live domain
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /** Only active equipment with stock can be borrowed. */
    public function scopeBorrowable($query)
    {
        return $query->where('status', 'active')->where('available_quantity', '>', 0);
    }

    public static function nextCode(): string
    {
        $next = (int) static::max('id') + 1;
        return 'EQ-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
