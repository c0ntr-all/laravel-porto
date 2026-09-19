<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum MovieTypeEnum: string
{
    use Arrayable;

    case MOVIE = 'movie';
    case TV_SERIES = 'tv_series';
    case SHOW = 'show';

    public static function fromKinopoisk(?string $raw, ?string $url = null): self
    {
        $haystack = mb_strtolower(trim((string) $raw.' '.$url));

        if (
            str_contains($haystack, 'tvseries')
            || str_contains($haystack, 'tv_series')
            || str_contains($haystack, 'tv-series')
            || str_contains($haystack, 'animated-series')
            || str_contains($haystack, 'mini_series')
            || str_contains($haystack, 'miniseries')
            || str_contains($haystack, 'сериал')
            || str_contains((string) $url, '/series/')
        ) {
            return self::TV_SERIES;
        }

        if (
            str_contains($haystack, 'шоу')
            || str_contains($haystack, 'tv_show')
            || str_contains($haystack, 'tvshow')
            || str_contains($haystack, 'tv-show')
        ) {
            return self::SHOW;
        }

        return self::MOVIE;
    }
}
