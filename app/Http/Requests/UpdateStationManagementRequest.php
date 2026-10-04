<?php

namespace App\Http\Requests;

use App\Enums\StationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStationManagementRequest extends FormRequest
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
    protected function prepareForValidation(): void
    {
        if (! $this->has('working_hours') || ! is_array($this->working_hours)) {
            return;
        }

        $workingHours = $this->input('working_hours', []);

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

        $this->merge([
            'working_hours' => $workingHours,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'address' => ['sometimes', 'string', 'max:65535'],
            'city' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'facilities' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'contact_info' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::enum(StationStatus::class)],
            'working_hours' => ['sometimes', 'array'],
            'working_hours.*.opens_at' => ['sometimes', 'nullable', 'date_format:H:i'],
            'working_hours.*.closes_at' => ['sometimes', 'nullable', 'date_format:H:i'],
            'working_hours.*.is_opened' => ['sometimes', 'nullable', 'boolean'],
            'default_grace_period_minutes' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'default_overstay_fee_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999.99'],
            'default_overstay_interval_minutes' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'cancellation_window_minutes' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'no_show_period_days' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'avg_rating' => ['sometimes', 'numeric', 'min:0', 'max:5'],
        ];
    }
}
