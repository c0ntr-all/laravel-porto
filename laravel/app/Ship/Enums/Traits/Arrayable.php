<?php declare(strict_types=1);

namespace App\Ship\Enums\Traits;

trait Arrayable
{
    public static function toArray(bool $withKeys = false): array
    {
        $out = [];

        foreach (static::cases() as $case) {
            $out[$case->name] = $case->value;
        }

        if ($withKeys) {
            return $out;
        } else {
            return array_values($out);
        }
    }
}
