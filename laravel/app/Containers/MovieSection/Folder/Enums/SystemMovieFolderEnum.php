<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Enums;

enum SystemMovieFolderEnum: string
{
    case WATCHLIST = 'watchlist';
    case WATCHED = 'watched';
    case FAVORITES = 'favorites';

    public function folderName(): string
    {
        return match ($this) {
            self::WATCHLIST => 'Буду смотреть',
            self::WATCHED => 'Просмотрено',
            self::FAVORITES => 'Любимые фильмы',
        };
    }
}
