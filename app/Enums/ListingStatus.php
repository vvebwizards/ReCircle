<?php

namespace App\Enums;

enum ListingStatus: string
{
    case ACTIVE = 'active';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
