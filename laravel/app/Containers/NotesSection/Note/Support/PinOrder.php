<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Support;

/**
 * Gap positions so a pinned note can move between two neighbors
 * by rewriting only its own pin_position. A full rewrite of the
 * pinned set happens only when the integer gap is exhausted.
 */
final class PinOrder
{
    public const GAP = 1024;

    public const MAX = 4_294_967_295;

    public static function between(?int $prev, ?int $next): ?int
    {
        if ($prev === null && $next === null) {
            return self::GAP;
        }

        if ($prev === null) {
            return $next !== null && $next > 1 ? intdiv($next, 2) : null;
        }

        if ($next === null) {
            $candidate = $prev + self::GAP;

            return $candidate <= self::MAX ? $candidate : null;
        }

        if ($next - $prev < 2) {
            return null;
        }

        return $prev + intdiv($next - $prev, 2);
    }
}
