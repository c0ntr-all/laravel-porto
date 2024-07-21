<?php

namespace App\Repositories;

use App\Models\Music\Album;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class AlbumRepository
{
    public function getWithPaginate(): LengthAwarePaginator
    {
        return QueryBuilder::for(Album::class)
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->paginate(100);
    }
}
