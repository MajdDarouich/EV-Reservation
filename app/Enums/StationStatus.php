<?php

namespace App\Enums;

enum StationStatus: string
{
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';
    case MAINTENANCE = 'Maintenance';
}
