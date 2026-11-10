<?php

namespace App\Enums;

enum InquiryType: string
{
    case Interest = 'interest';
    case Contact = 'contact';
    case Sell = 'sell';
    case TestDrive = 'test_drive';

    public function label(): string
    {
        return match ($this) {
            self::Interest => 'Interest',
            self::Contact => 'Contact',
            self::Sell => 'Sell your car',
            self::TestDrive => 'Test drive',
        };
    }
}
