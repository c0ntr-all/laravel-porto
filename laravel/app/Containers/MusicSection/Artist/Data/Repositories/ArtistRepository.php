<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Data\Repositories;

use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ArtistRepository
{
    public static function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Artist::class)
                           ->orderByDesc('created_at')
                           ->cursorPaginate(12);
    }

    public static function getWithPaginate(): LengthAwarePaginator
    {
        return QueryBuilder::for(Artist::class)
                           ->orderByDesc('created_at')
                           ->paginate(100);
    }

    public function update(Artist $artist, UpdateArtistDto $dto): Artist
    {
        $artist->update($dto->toArray());

        return $artist;
    }
}
