<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case QA = 'qa';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
