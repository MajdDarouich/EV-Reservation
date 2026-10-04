<?php

namespace App\Http\Requests;

use App\Enums\StationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStationManagementRequest extends FormRequest
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
        $workingHours = $this->input('working_hours');

        if (! is_array($workingHours)) {
            return;
        }

        foreach ($workingHours as $day => $slot) {
            if (! is_array($slot)) {
                continue;
            }

            foreach (['opens_at', 'closes_at'] as $field) {
                $value = $slot[$field] ?? null;

                if (isset($value) && trim((string) $value) === '') {
                    $workingHours[$day][$field] = null;
                } elseif (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                    $workingHours[$day][$field] = substr($value, 0, 5);
                }
            }
        }

        $this->merge(['working_hours' => $workingHours]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'address' => ['required', 'string', 'max:65535'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'facilities' => ['nullable', 'string', 'max:65535'],
            'contact_info' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(StationStatus::class)],
            'working_hours' => ['required', 'array'],
            'working_hours.*.opens_at' => ['sometimes', 'nullable', 'date_format:H:i'],
            'working_hours.*.closes_at' => ['sometimes', 'nullable', 'date_format:H:i'],
            'working_hours.*.is_opened' => ['sometimes', 'nullable', 'boolean'],
            'default_grace_period_minutes' => ['required', 'integer', 'min:0', 'max:65535'],
            'default_overstay_fee_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'default_overstay_interval_minutes' => ['required', 'integer', 'min:1', 'max:65535'],
            'cancellation_window_minutes' => ['required', 'integer', 'min:0', 'max:65535'],
            'no_show_period_days' => ['required', 'integer', 'min:0', 'max:65535'],
            'avg_rating' => ['required', 'numeric', 'min:0', 'max:5'],
        ];
    }
}
