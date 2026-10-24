<?php

namespace App\Models;

use App\Enums\ChargerStatus;
use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    protected $fillable = [
        'station_id',
        'code',
        'manufacturer',
        'model',
        'current_type',
        'power_kw',
        'serial_number',
        'firmware_version',
        'status',
        'default_overstay_fee_amount',
        'default_overstay_interval_minutes',
    ];

    protected $casts = [
        'status' => ChargerStatus::class,
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function connectors()
    {
        return $this->hasMany(Connector::class);
    }
}
