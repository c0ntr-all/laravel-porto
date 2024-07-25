<?php

namespace App\Containers\Music\Data\Repositories;

use App\Containers\Music\Models\Artist;
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
}
