<?php

namespace App\Console\Commands;

use App\Enums\ChargingStatus;
use App\Models\ChargingSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimulateCharging extends Command
{
    protected $signature = 'charging:simulate';

    protected $description = 'Advance in-progress charging sessions by one ten-minute simulation tick';

    public function handle(): int
    {
        $intervalMinutes = (int) config('charging.simulation_interval_minutes', 10);
        $simulatedCount = 0;

        ChargingSession::query()
            ->where('charging_status', ChargingStatus::IN_PROGRESS->value)
            ->with('reservation.vehicle', 'reservation.connector.charger.station')
            ->chunkById(100, function ($sessions) use ($intervalMinutes, &$simulatedCount): void {
                foreach ($sessions as $session) {
                    $didSimulate = DB::transaction(function () use (
                        $session,
                        $intervalMinutes
                    ): bool {
                        $session = ChargingSession::query()
                            ->whereKey($session->id)
                            ->where('charging_status', ChargingStatus::IN_PROGRESS->value)
                            ->lockForUpdate()
                            ->with('reservation.vehicle', 'reservation.connector.charger.station')
                            ->first();

                        if (! $session || ! $this->isDue($session, $intervalMinutes)) {
                            return false;
                        }

                        $this->advance($session, $intervalMinutes);

                        return true;
                    });

                    if ($didSimulate) {
                        $simulatedCount++;
                    }
                }
            });

        $this->info("Simulated {$simulatedCount} charging session(s).");

        return self::SUCCESS;
    }

    private function isDue(ChargingSession $session, int $intervalMinutes): bool
    {
        return ! $session->last_simulated_at
            || $session->last_simulated_at->lte(now()->subMinutes($intervalMinutes));
    }

    private function advance(ChargingSession $session, int $intervalMinutes): void
    {
        $vehicle = $session->reservation?->vehicle;
        $charger = $session->reservation?->connector?->charger;

        if (! $vehicle || ! $charger || (float) $vehicle->battery_capacity_kwh <= 0) {
            $this->error("Session {$session->id} is missing vehicle battery or charger data.");

            return;
        }

        $currentSoc = (float) $session->current_SOC;
        $targetSoc = (float) $session->target_SOC;
        $batteryCapacity = (float) $vehicle->battery_capacity_kwh;
        $energyForTick = (float) $session->actual_power_kW * $intervalMinutes / 60;
        $remainingEnergy = max(0, $batteryCapacity * ($targetSoc - $currentSoc) / 100);
        $energyDelivered = min($energyForTick, $remainingEnergy);
        $newSoc = min($targetSoc, $currentSoc + ($energyDelivered / $batteryCapacity * 100));
        $price = (float) ($charger->station?->price_per_kwh
            ?? config('charging.prices_per_kwh.'.strtoupper($charger->current_type), 0));
        $energyCost = (float) $session->energy_cost + ($energyDelivered * $price);

        $session->forceFill([
            'current_SOC' => round($newSoc, 2),
            'energy_delivered_kWh' => (float) $session->energy_delivered_kWh + $energyDelivered,
            'energy_cost' => round($energyCost, 2),
            'final_cost' => round($energyCost + (float) $session->overstay_fee, 2),
            'last_simulated_at' => now(),
        ])->save();

        if ($newSoc >= $targetSoc && ! $session->target_reached_at) {
            $session->forceFill(['target_reached_at' => now()])->save();
            Log::warning('Charging session reached target SOC and requires a manual stop.', [
                'charging_session_id' => $session->id,
                'target_soc' => $targetSoc,
            ]);
            $this->warn("Session {$session->id} reached its target SOC; manual stop is required.");
        }
    }
}
