<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargingType extends Model
{
    protected $fillable = [
        'code',
        'current_type',
        'power_kwh',
    ];

    public function chargers()
    {
        return $this->hasMany(Charger::class);
    }
}
