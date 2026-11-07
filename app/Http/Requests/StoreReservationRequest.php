<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
            'vehicle_name' => ['required', 'string', 'max:255'],
            'license_plate' => ['required', 'string', 'max:255'],
            'station_name' => ['required', 'string', 'max:255'],
            'power_input' => ['required', 'string', 'in:AC,DC,ac,dc'],
            'current_soc' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_soc' => ['required', 'numeric', 'min:0', 'max:100', 'gte:current_soc'],
            'charger_id' => ['required', 'integer', 'exists:chargers,id'],
            'connector_id' => ['required', 'integer', 'exists:connectors,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }
}
