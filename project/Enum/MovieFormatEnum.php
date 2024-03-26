<?php

namespace App\Enum;

enum MovieFormatEnum: string
{
    case VHS = 'VHS';
    case DVD = 'DVD';
    case BLU_RAY = 'Blu-ray';

    public static function getValues(): array
    {
        return[
            self::VHS->value,
            self::DVD->value,
            self::BLU_RAY->value
        ];
    }
}
