<?php declare(strict_types=1);

namespace App\Ship\Helpers;

use Illuminate\Support\Str;

final class UuidV7
{
    public static function generate(): string
    {
        return (string) Str::uuid7();
    }
}
