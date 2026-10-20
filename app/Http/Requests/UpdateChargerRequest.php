<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChargerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_overstay_fee_amount' => ['required', 'numeric', 'min:0'],
            'default_overstay_interval_minutes' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
