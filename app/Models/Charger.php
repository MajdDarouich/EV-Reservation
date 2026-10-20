<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    protected $fillable = [
        'station_id',
        'charging_type_id',
        'serial_number',
        'manufacturer',
        'model',
        'firmware_version',
        'status',
        'default_overstay_fee_amount',
        'default_overstay_interval_minutes',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function chargingType()
    {
        return $this->belongsTo(ChargingType::class);
    }
}
