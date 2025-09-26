<?php

namespace App\Enums;

enum PickupStatus: string
{
    case SCHEDULED = 'scheduled';
    case IN_TRANSIT = 'in_transit';
    case PICKED_UP = 'picked_up';
    case DELAYED = 'delayed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
}
