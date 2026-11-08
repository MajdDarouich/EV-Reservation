<?php

namespace App\Models;

use App\Enums\ChargingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargingSession extends Model
{
    protected $fillable = [
        'reservation_id',
        'actual_start_time',
        'actual_end_time',
        'start_SOC',
        'current_SOC',
        'target_SOC',
        'energy_delivered_kWh',
        'actual_power_kW',
        'charging_status',
        'estimated_end_time',
        'estimated_duration_minutes',
        'cancellation_reason',
        'cancelled_by',
        'last_simulated_at',
        'target_reached_at',
        'energy_cost',
        'overstay_fee',
        'final_cost',
    ];

    protected $casts = [
        'charging_status' => ChargingStatus::class,
        'last_simulated_at' => 'datetime',
        'target_reached_at' => 'datetime',
        'energy_delivered_kWh' => 'decimal:2',
        'actual_power_kW' => 'decimal:2',
        'energy_cost' => 'decimal:2',
        'overstay_fee' => 'decimal:2',
        'final_cost' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
