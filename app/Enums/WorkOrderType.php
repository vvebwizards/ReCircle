<?php

namespace App\Enums;

enum WorkOrderType: string
{
    case PREPROCESSING = 'preprocessing';
    case FABRICATION = 'fabrication';
    case ASSEMBLY = 'assembly';
    case FINISHING = 'finishing';
}
