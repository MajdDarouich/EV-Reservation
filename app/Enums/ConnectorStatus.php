<?php

namespace App\Enums;

enum ConnectorStatus: string
{
    case AVAILABLE = 'Available';
    case OUT_OF_SERVICE = 'Out of Service';
}
