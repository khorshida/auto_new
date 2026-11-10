<?php

namespace App\Enums;

enum CarImageKind: string
{
    case Gallery = 'gallery';
    case Spin = 'spin';

    public function label(): string
    {
        return match ($this) {
            self::Gallery => 'Gallery',
            self::Spin => '360 spin',
        };
    }
}
