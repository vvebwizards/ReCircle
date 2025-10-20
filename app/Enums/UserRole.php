<?php

namespace App\Enums;

enum UserRole: string
{
    case BUYER = 'buyer';
    case GENERATOR = 'generator';
    case MAKER = 'maker';
    case COURIER = 'courier';
    case ADMIN = 'admin';
}
