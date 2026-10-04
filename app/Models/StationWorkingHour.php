<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StationWorkingHour extends Model
{
    protected $fillable = [
        'station_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'is_opened'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
