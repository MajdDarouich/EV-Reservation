<?php

namespace App\Models;

use App\Enums\ConnectorStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connector extends Model
{
    protected $casts = [
        'status' => ConnectorStatus::class,
    ];

    protected $fillable = [
        'charger_id',
        'code',
        'current_type',
        'number',
        'max_power_kw',
        'status',
    ];

    public function charger()
    {
        return $this->belongsTo(Charger::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
