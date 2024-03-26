<?php

namespace App\Enum;

enum CSRFRequestMethodsEnum: string
{
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'Delete';

    public static function isNeedCSRF(string $value): bool
    {
       return match ($value) {
           self::Post->value,
           self::Put->value,
           self::Patch->value,
           self::Delete->value => true,
           default => false,
       };
    }
}
