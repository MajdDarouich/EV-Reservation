<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'Active';
    case SUSPENDED = 'Suspended';
    case DELETED = 'Deleted';
}
