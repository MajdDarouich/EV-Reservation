<?php

namespace App\Enums;

enum ChargingStatus: string
{
    case IN_PROGRESS = 'In Progress';
    case COMPLETED = 'Completed';
    case INTERRUPTED = 'Interrupted';
}
