<?php

namespace App\Support;

class Wishlist
{
    public const KEY = 'wishlist';

    /**
     * @return list<int>
     */
    public static function ids(): array
    {
        $ids = session(self::KEY, []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_map(intval(...), $ids));
    }

    public static function contains(int $carId): bool
    {
        return in_array($carId, self::ids(), true);
    }

    public static function add(int $carId): void
    {
        $ids = self::ids();

        if (in_array($carId, $ids, true)) {
            return;
        }

        $ids[] = $carId;
        session([self::KEY => $ids]);
    }

    public static function remove(int $carId): void
    {
        session([
            self::KEY => array_values(array_filter(
                self::ids(),
                fn (int $id): bool => $id !== $carId,
            )),
        ]);
    }
}
