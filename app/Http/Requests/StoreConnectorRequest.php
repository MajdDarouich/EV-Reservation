<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConnectorRequest extends FormRequest
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
        $charger = $this->route('charger');

        return [
            'connector_id' => [
                'required',
                'integer',
                Rule::exists('connectors', 'id')
                    ->whereNull('charger_id')
                    ->where('current_type', $charger->current_type),
            ],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                Rule::unique('connectors', 'number')->where(
                    fn ($query) => $query->where('charger_id', $this->route('charger')->id)
                ),
            ],
        ];
    }
}
