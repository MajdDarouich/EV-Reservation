<?php

namespace App\Http\Requests\Api;

use App\Support\PhoneNumberNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class RegisterApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone_number')) {
            $this->merge([
                'phone_number' => PhoneNumberNormalizer::normalize($this->phone_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
    * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
