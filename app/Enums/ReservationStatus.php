<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case PENDING = 'Pending';
    case CONFIRMED = 'Confirmed';
    case CANCELLED = 'Cancelled';
    case NO_SHOW = 'No Show';
    case COMPLETED = 'Completed';
}