<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreChargerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'charger_ids' => ['required', 'array', 'min:1'],
            'charger_ids.*' => ['integer', 'distinct', 'exists:chargers,id'],
        ];
    }
}
