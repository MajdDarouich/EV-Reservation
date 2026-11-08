<?php

namespace App\Services\Api;

use App\Enums\ChargingStatus;
use App\Enums\ReservationStatus;
use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationLifecycleService
{
    public function cancelByUser(int $reservationId): Reservation
    {
        /** @var User $user */
        $user = Auth::user();

        return DB::transaction(function () use ($reservationId, $user): Reservation {
            $reservation = $user->reservations()
                ->whereKey($reservationId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($reservation->status !== ReservationStatus::CONFIRMED) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only confirmed reservations can be cancelled.',
                ]);
            }

            if ($reservation->chargingSession()->exists()) {
                throw ValidationException::withMessages([
                    'reservation' => 'A reservation with an active charging session cannot be cancelled.',
                ]);
            }

            $isLate = now()->greaterThan(Carbon::parse($reservation->cancellation_deadline));
            $reservation->forceFill([
                'status' => ReservationStatus::CANCELLED,
                'cancelled_by' => (string) $user->id,
                'cancellation_reason' => $isLate ? 'LateCancellation' : null,
                'cancelled_at' => now(),
            ])->save();

            if ($isLate) {
                $user->increment('no_show_count');
            }

            return $reservation->refresh();
        });
    }

    public function cancelByStaff(int $reservationId): Reservation
    {
        return DB::transaction(function () use ($reservationId): Reservation {
            $reservation = Reservation::query()
                ->lockForUpdate()
                ->findOrFail($reservationId);

            if ($reservation->status !== ReservationStatus::CONFIRMED) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only confirmed reservations can be cancelled.',
                ]);
            }

            if ($reservation->chargingSession()->exists()) {
                throw ValidationException::withMessages([
                    'reservation' => 'A reservation cannot be cancelled after charging has started.',
                ]);
            }

            $reservation->forceFill([
                'status' => ReservationStatus::CANCELLED,
                'cancelled_by' => 'station_staff',
                'cancellation_reason' => 'CancelledByStation',
                'cancelled_at' => now(),
            ])->save();

            return $reservation->refresh();
        });
    }

    public function startCharging(int $reservationId): ChargingSession
    {
        return DB::transaction(function () use ($reservationId): ChargingSession {
            $reservation = Reservation::query()
                ->with(['vehicle', 'connector.charger'])
                ->lockForUpdate()
                ->findOrFail($reservationId);

            if ($reservation->status !== ReservationStatus::CONFIRMED) {
                throw ValidationException::withMessages([
                    'reservation' => 'Only confirmed reservations can start charging.',
                ]);
            }

            $now = now();
            $deadline = Carbon::parse($reservation->date.' '.$reservation->arrival_deadline);
            if ($now->greaterThan($deadline)) {
                $this->cancelForLateArrival($reservation);

                throw ValidationException::withMessages([
                    'reservation' => 'The arrival deadline has passed. The reservation was cancelled as LateArrival.',
                ]);
            }

            if ($reservation->chargingSession()->exists()) {
                throw ValidationException::withMessages([
                    'reservation' => 'Charging has already started for this reservation.',
                ]);
            }

            $vehicle = $reservation->vehicle;
            $charger = $reservation->connector?->charger;
            $power = min(
                (float) $charger?->power_kw,
                strtoupper((string) $charger?->current_type) === 'AC'
                    ? (float) $vehicle?->ac_max_kw
                    : (float) $vehicle?->dc_max_kw
            );

            if (! $vehicle || ! $charger || $power <= 0) {
                throw ValidationException::withMessages([
                    'reservation' => 'The reservation is missing valid vehicle or charger data.',
                ]);
            }

            $session = ChargingSession::create([
                'reservation_id' => $reservation->id,
                'actual_start_time' => $now->format('H:i:s'),
                'start_SOC' => $reservation->current_SOC_at_booking,
                'current_SOC' => $reservation->current_SOC_at_booking,
                'target_SOC' => $reservation->target_SOC,
                'energy_delivered_kWh' => 0,
                'actual_power_kW' => $power,
                'charging_status' => ChargingStatus::IN_PROGRESS,
                'estimated_end_time' => $reservation->estimated_end_time,
                'estimated_duration_minutes' => $reservation->estimated_duration_minutes,
            ]);

            $reservation->forceFill(['arrival_confirmed_at' => $now])->save();
            $reservation->user()->update(['no_show_count' => 0]);

            return $session->refresh();
        });
    }

    public function stopCharging(int $reservationId, ?string $reason = null): ChargingSession
    {
        return DB::transaction(function () use ($reservationId, $reason): ChargingSession {
            $reservation = Reservation::query()
                ->with('connector.charger')
                ->lockForUpdate()
                ->findOrFail($reservationId);
            $session = $reservation->chargingSession()->lockForUpdate()->first();

            if (! $session || $session->charging_status !== ChargingStatus::IN_PROGRESS) {
                throw ValidationException::withMessages([
                    'reservation' => 'This reservation does not have an active charging session.',
                ]);
            }

            $endedAt = now();
            $overstayFee = $this->overstayFee($session, $reservation, $endedAt);
            $session->forceFill([
                'actual_end_time' => $endedAt->format('H:i:s'),
                'charging_status' => ChargingStatus::COMPLETED,
                'cancellation_reason' => $reason,
                'overstay_fee' => $overstayFee,
                'final_cost' => (float) $session->energy_cost + $overstayFee,
            ])->save();
            $reservation->forceFill(['status' => ReservationStatus::COMPLETED])->save();

            return $session->refresh();
        });
    }

    public function processNoShows(): int
    {
        $count = 0;
        Reservation::query()
            ->where('status', ReservationStatus::CONFIRMED)
            ->whereNull('arrival_confirmed_at')
            ->whereDoesntHave('chargingSession')
            ->chunkById(100, function ($reservations) use (&$count): void {
                foreach ($reservations as $reservation) {
                    $deadline = Carbon::parse($reservation->date.' '.$reservation->arrival_deadline);
                    if (now()->lessThanOrEqualTo($deadline)) {
                        continue;
                    }

                    DB::transaction(function () use ($reservation, &$count): void {
                        $locked = Reservation::query()->lockForUpdate()->find($reservation->id);
                        if (! $locked || $locked->status !== ReservationStatus::CONFIRMED
                            || $locked->arrival_confirmed_at || $locked->chargingSession()->exists()) {
                            return;
                        }

                        $locked->forceFill([
                            'status' => ReservationStatus::NO_SHOW,
                            'cancellation_reason' => 'NoShow',
                        ])->save();
                        $locked->user()->increment('no_show_count');
                        $count++;
                    });
                }
            });

        return $count;
    }

    private function cancelForLateArrival(Reservation $reservation): void
    {
        $reservation->forceFill([
            'status' => ReservationStatus::CANCELLED,
            'cancellation_reason' => 'LateArrival',
            'cancelled_at' => now(),
        ])->save();
        $reservation->user()->increment('no_show_count');
    }

    private function overstayFee(ChargingSession $session, Reservation $reservation, Carbon $endedAt): float
    {
        if (! $session->target_reached_at || $endedAt->lessThanOrEqualTo($session->target_reached_at)) {
            return 0;
        }

        $charger = $reservation->connector?->charger;
        $interval = (int) ($charger?->default_overstay_interval_minutes ?? 0);
        $amount = (float) ($charger?->default_overstay_fee_amount ?? 0);
        if ($interval <= 0 || $amount <= 0) {
            return 0;
        }

        $minutes = $session->target_reached_at->diffInMinutes($endedAt);

        return ceil($minutes / $interval) * $amount;
    }
}
