<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $casts = [
        'status' => ReservationStatus::class,
    ];

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'connector_id',
        'date',
        'start_time',
        'arrival_deadline',
        'current_SOC_at_booking',
        'target_SOC',
        'energy_required_kWh',
        'estimated_duration_minutes',
        'estimated_end_time',
        'status',
        'cancelled_by',
        'cancellation_reason',
        'cancellation_deadline',
        'cancelled_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }
}
