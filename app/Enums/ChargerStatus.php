<?php

namespace App\Enums;

enum ChargerStatus: string
{
    case AVAILABLE = 'Available';
    case BUSY = 'Busy';
    case RESERVED = 'Reserved';
    case CHARGING_COMPLETED = 'Charging Completed';
    case OUT_OF_SERVICE = 'Out of Service';
}
