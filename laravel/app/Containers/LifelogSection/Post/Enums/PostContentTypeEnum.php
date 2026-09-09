<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum PostContentTypeEnum: string
{
    use Arrayable;

    case DEFAULT = 'default';
    case MUSIC = 'music';
    case MOVIE = 'movie';
    case TV_SERIES = 'tv_series';
    case GAME = 'game';

    public static function default(): self
    {
        return self::DEFAULT;
    }

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Обычный',
            self::MUSIC => 'Музыка',
            self::MOVIE => 'Фильм',
            self::TV_SERIES => 'Сериал',
            self::GAME => 'Игра',
        };
    }
}
