<?php

namespace App\Enums;

enum CarCondition: string
{
    case BrandNew = 'brand_new';
    case PreOwned = 'pre_owned';
    case Reconditioned = 'reconditioned';
    case Electric = 'electric';

    public function label(): string
    {
        return match ($this) {
            self::BrandNew => 'Brand new',
            self::PreOwned => 'Pre-owned',
            self::Reconditioned => 'Reconditioned',
            self::Electric => 'Electric',
        };
    }
}
