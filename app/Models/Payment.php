<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'charging_session_id',
        'payment_method',
        'status',
        'paid_at',
        'stripe_checkout_session_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function chargingSession(): BelongsTo
    {
        return $this->belongsTo(ChargingSession::class);
    }
}
