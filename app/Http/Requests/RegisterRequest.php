<?php

namespace App\Http\Requests;

use App\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->email)), 'id_number' => trim((string) $this->id_number)]);
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'id_number' => ['required', 'string', 'max:50', 'unique:users,id_number'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'password'  => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            // reCAPTCHA is enforced only when keys are configured in .env
            'g-recaptcha-response' => config('services.recaptcha.secret') ? ['required', new Recaptcha] : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'     => 'This email address is already registered.',
            'id_number.unique' => 'This Student / Employee ID is already registered.',
            'g-recaptcha-response.required' => 'Please tick the "I\'m not a robot" box.',
        ];
    }
}
