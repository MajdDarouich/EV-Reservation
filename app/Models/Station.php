<?php

namespace App\Models;

use App\Enums\StationStatus;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'facilities',
        'contact_info',
        'status',
        'default_grace_period_minutes',
        'default_overstay_fee_amount',
        'default_overstay_interval_minutes',
        'cancellation_window_minutes',
        'no_show_period_days',
        'avg_rating'
    ];

    public function stationWorkingHours()
    {
        return $this->hasMany(StationWorkingHour::class);
    }

    protected $casts = [
        'status' => StationStatus::class,
    ];

}
