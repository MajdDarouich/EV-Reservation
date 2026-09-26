<?php

namespace App\Http\Requests;

use App\Support\PhoneNumberNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'exists:users,phone_number'],
        ];
    }
}
