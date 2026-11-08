<?php

namespace App\Services\Api;

use App\Enums\ChargerStatus;
use App\Enums\ConnectorStatus;
use App\Enums\ReservationStatus;
use App\Models\Connector;
use App\Models\Reservation;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->reservations()
            ->with([
                'vehicle',
                'connector.charger.station',
            ])
            ->get();
    }

    public function store(array $data)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->no_show_count >= $user->no_show_limit) {
            throw ValidationException::withMessages([
                'reservation' => 'Reservations are temporarily suspended for this user. Please contact station administration.',
            ]);
        }
        $vehicle = $user->vehicles()
            ->where('model', $data['vehicle_name'])
            ->where('license_plate', $data['license_plate'])
            ->firstOrFail();
        $station = Station::where('name', $data['station_name'])->firstOrFail();
        $powerInput = strtoupper($data['power_input']);
        $connector = Connector::query()
            ->whereKey($data['connector_id'])
            ->where('charger_id', $data['charger_id'])
            ->with('charger')
            ->first();

        if (! $connector) {
            throw ValidationException::withMessages([
                'connector_id' => 'The selected connector does not exist or does not belong to the selected charger.',
            ]);
        }

        $charger = $connector?->charger;

        if (! $charger || $charger->station_id !== $station->id) {
            throw ValidationException::withMessages([
                'charger_id' => 'The selected charger is not available at the selected station.',
            ]);
        }

        if ($charger->status->value !== ChargerStatus::AVAILABLE->value) {
            throw ValidationException::withMessages([
                'charger_id' => 'The selected charger is not available.',
            ]);
        }

        if ($connector->status->value !== ConnectorStatus::AVAILABLE->value) {
            throw ValidationException::withMessages([
                'connector_id' => 'The selected connector is not available.',
            ]);
        }

        if (
            $connector->code !== $vehicle->connector_type
            || strtoupper($connector->current_type) !== $powerInput
            || strtoupper($charger->current_type) !== $powerInput
        ) {
            throw ValidationException::withMessages([
                'connector_id' => 'The selected charger and connector are not compatible with this vehicle.',
            ]);
        }

        $chargingDetails = $this->calculateChargingDetails(
            $vehicle,
            $charger,
            $data['power_input'],
            (float) $data['current_soc'],
            (float) $data['target_soc'],
        );
        $start = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['date'].' '.$data['start_time']
        );
        $arrivalDeadline = $start->copy()->addMinutes(10);
        $cancellationDeadline = $start->copy()->subHour();
        $end = $start->copy()->addMinutes($chargingDetails['charging_time_minutes']);

        $conflictingReservation = Reservation::query()
            ->where('connector_id', $connector->id)
            ->whereDate('date', $start->toDateString())
            ->where('status', '!=', 'Cancelled')
            ->where('start_time', '<', $end->format('H:i:s'))
            ->where('estimated_end_time', '>', $start->format('H:i:s'))
            ->exists();

        if ($conflictingReservation) {
            throw ValidationException::withMessages([
                'start_time' => 'The selected connector is already reserved during this time.',
            ]);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'connector_id' => $connector->id,
            'date' => $start->toDateString(),
            'start_time' => $start->format('H:i:s'),
            'arrival_deadline' => $arrivalDeadline->format('H:i:s'),
            'current_SOC_at_booking' => $data['current_soc'],
            'target_SOC' => $data['target_soc'],
            'energy_required_kWh' => $chargingDetails['required_energy_kWh'],
            'estimated_duration_minutes' => $chargingDetails['charging_time_minutes'],
            'estimated_end_time' => $end->format('H:i:s'),
            'cancellation_deadline' => $cancellationDeadline->format('Y-m-d H:i:s'),
        ]);

        return [
            'reservation' => $reservation,
            'selected_charger' => $charger,
            'selected_connector' => $connector,
            ...$chargingDetails,
        ];
    }

    public function cancel(int $reservationId): Reservation
    {
        /** @var User $user */
        $user = Auth::user();
        $reservation = $user->reservations()->findOrFail($reservationId);
        $isLateCancellation = Carbon::now()->greaterThan(
            Carbon::parse($reservation->cancellation_deadline)
        );

        DB::transaction(function () use ($reservation, $user, $isLateCancellation): void {
            $reservation->forceFill([
                'status' => ReservationStatus::CANCELLED,
                'cancelled_by' => (string) $user->id,
                'cancellation_reason' => $isLateCancellation ? 'LateCancellation' : null,
                'cancelled_at' => Carbon::now(),
            ])->save();

            if ($isLateCancellation) {
                $user->increment('no_show_count');
            }

        });

        return $reservation->refresh();
    }

    public function chooseCharger(array $data)
    {
        /** @var User $user */
        $user = Auth::user();
        $vehicle = $user->vehicles()->findOrFail($data['vehicle_id']);
        $station = Station::findOrFail($data['station_id']);
        $powerInput = strtoupper($data['power_input']);

        $chargers = $this->compatibleChargers($station, $vehicle, $powerInput)->get();

        if ($chargers->isEmpty()) {
            throw ValidationException::withMessages([
                'station_id' => 'No compatible chargers found at the selected station.',
            ]);
        }

        return [
            'chargers' => $chargers,
            'selected_charger' => $chargers->first(),
            'selected_connector' => $chargers->first()->connectors->first(),
        ];
    }

    private function compatibleChargers(
        Station $station,
        $vehicle,
        string $powerInput
    ) {
        $powerInput = strtoupper($powerInput);
        if (! in_array($powerInput, ['AC', 'DC'], true)) {
            throw ValidationException::withMessages([
                'power_input' => 'Power input must be AC or DC.',
            ]);
        }

        return $station->chargers()
            ->whereHas('connectors', function ($query) use ($vehicle, $powerInput) {
                $query
                    ->where('status', ConnectorStatus::AVAILABLE->value)
                    ->where('code', $vehicle->connector_type)
                    ->where('current_type', $powerInput);
            })
            ->where('status', ChargerStatus::AVAILABLE->value)
            ->where('current_type', $powerInput)
            ->with([
                'connectors' => function ($query) use ($vehicle, $powerInput) {
                    $query
                        ->where('status', ConnectorStatus::AVAILABLE->value)
                        ->where('code', $vehicle->connector_type)
                        ->where('current_type', $powerInput)
                        ->orderBy('id');
                },
            ])
            ->orderBy('id');
    }

    private function calculateChargingDetails(
        $vehicle,
        $charger,
        string $powerInput,
        float $currentSoc,
        float $targetSoc
    ): array {
        $vehicleMaximumPower = match (strtoupper($powerInput)) {
            'AC' => (float) $vehicle->ac_max_kw,
            'DC' => (float) $vehicle->dc_max_kw,
            default => throw ValidationException::withMessages([
                'power_input' => 'Power input must be AC or DC.',
            ]),
        };
        $requiredEnergy = (float) $vehicle->battery_capacity_kwh
            * ($targetSoc - $currentSoc) / 100;
        $actualPower = min((float) $charger->power_kw, $vehicleMaximumPower);

        if ($actualPower <= 0) {
            throw ValidationException::withMessages([
                'power_input' => 'The selected charger and vehicle must have a positive charging power.',
            ]);
        }

        return [
            'required_energy_kWh' => round($requiredEnergy, 2),
            'actual_power_kw' => round($actualPower, 2),
            'charging_time_hours' => round($requiredEnergy / $actualPower, 2),
            'charging_time_minutes' => (int) ceil(($requiredEnergy / $actualPower) * 60),
        ];
    }
}
