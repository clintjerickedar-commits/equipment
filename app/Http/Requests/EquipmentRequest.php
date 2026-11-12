<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:150'],
            'category_id'        => ['required', 'exists:categories,id'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'quantity'           => ['required', 'integer', 'min:0', 'max:100000'],
            'condition'          => ['required', Rule::in(['good', 'fair', 'needs_repair'])],
            'status'             => ['required', Rule::in(['active', 'maintenance', 'retired'])],
            // Secure upload: real image types only, max 2 MB
            'image'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
