<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'vin',
        'license_plate',
        'manufacturer',
        'model',
        'model_year',
        'battery_capacity_kwh',
        'ac_max_kw',
        'dc_max_kw',
        'connector_type',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
