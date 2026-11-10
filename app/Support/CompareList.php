<?php

namespace App\Support;

class CompareList
{
    public const KEY = 'compare';

    public const LIMIT = 3;

    /**
     * @return list<int>
     */
    public static function ids(): array
    {
        $ids = session(self::KEY, []);

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_slice(array_map(intval(...), $ids), 0, self::LIMIT));
    }

    public static function contains(int $carId): bool
    {
        return in_array($carId, self::ids(), true);
    }

    public static function add(int $carId): bool
    {
        $ids = self::ids();

        if (in_array($carId, $ids, true)) {
            return true;
        }

        if (count($ids) >= self::LIMIT) {
            return false;
        }

        $ids[] = $carId;
        session([self::KEY => $ids]);

        return true;
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

    /**
     * @param  list<int>  $ids
     */
    public static function replace(array $ids): void
    {
        session([self::KEY => array_values($ids)]);
    }
}
