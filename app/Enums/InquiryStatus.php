<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case FollowUp = 'follow_up';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::FollowUp => 'Follow up',
            self::Closed => 'Closed',
        };
    }
}
