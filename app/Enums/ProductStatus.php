<?php

namespace App\Enums;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SOLD_OUT = 'sold_out';
    case ARCHIVED = 'archived';


    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }
}

