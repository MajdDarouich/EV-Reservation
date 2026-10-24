<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConnectorRequest extends FormRequest
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
        $connector = $this->route('connector');
        $charger = $connector->charger;

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::exists('connectors', 'code')
                    ->where('current_type', $charger->current_type)
                    ->where('max_power_kw', $this->input('max_power_kw')),
            ],
            'current_type' => [
                'required',
                'string',
                'max:255',
                Rule::in([$charger->current_type]),
            ],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                Rule::unique('connectors', 'number')
                    ->where(fn ($query) => $query->where('charger_id', $charger->id))
                    ->ignore($connector->id),
            ],
            'max_power_kw' => ['required', 'numeric', 'min:0'],
        ];
    }
}
