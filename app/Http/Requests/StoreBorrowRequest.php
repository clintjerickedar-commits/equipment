<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && !$this->user()->isAdmin();
    }

    public function rules(): array
    {
        $maxDays = (int) Setting::get('max_borrow_days');        // 0 = no limit
        $maxQty  = (int) Setting::get('max_quantity_per_item');  // 0 = no limit

        $qtyRules = ['required', 'integer', 'min:1'];
        if ($maxQty > 0) {
            $qtyRules[] = 'max:' . $maxQty;
        }

        return [
            'borrow_date'          => ['required', 'date', 'after_or_equal:today'],
            'expected_return_date' => ['required', 'date', 'after_or_equal:borrow_date', function ($attribute, $value, $fail) use ($maxDays) {
                if ($maxDays <= 0) {
                    return;
                }
                try {
                    if (Carbon::parse($value)->gt(Carbon::parse($this->borrow_date)->addDays($maxDays))) {
                        $fail("Equipment can be borrowed for at most {$maxDays} day(s).");
                    }
                } catch (\Throwable $e) {
                    // an invalid borrow date is already reported by its own rule
                }
            }],
            'purpose'              => ['required', 'string', 'min:5', 'max:1000'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'integer', 'distinct', 'exists:equipment,id'],
            'items.*.quantity'     => $qtyRules,
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                => 'Add at least one equipment item.',
            'items.*.quantity.max'          => 'You can borrow at most :max unit(s) of one item per request.',
            'items.*.equipment_id.distinct' => 'You selected the same equipment twice. Combine the quantities in one row.',
        ];
    }
}
