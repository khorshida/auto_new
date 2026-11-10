<?php

namespace App\Enums;

enum BodyStyle: string
{
    case Sedan = 'sedan';
    case Suv = 'suv';
    case Coupe = 'coupe';
    case Hatchback = 'hatchback';
    case Convertible = 'convertible';
    case Wagon = 'wagon';

    public function label(): string
    {
        return match ($this) {
            self::Sedan => 'Sedan',
            self::Suv => 'SUV',
            self::Coupe => 'Coupe',
            self::Hatchback => 'Hatchback',
            self::Convertible => 'Convertible',
            self::Wagon => 'Wagon',
        };
    }
}
