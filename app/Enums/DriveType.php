<?php

namespace App\Enums;

enum DriveType: string
{
    case FrontWheel = 'fwd';
    case RearWheel = 'rwd';
    case AllWheel = 'awd';

    public function label(): string
    {
        return match ($this) {
            self::FrontWheel => 'Front-wheel drive',
            self::RearWheel => 'Rear-wheel drive',
            self::AllWheel => 'All-wheel drive',
        };
    }
}
