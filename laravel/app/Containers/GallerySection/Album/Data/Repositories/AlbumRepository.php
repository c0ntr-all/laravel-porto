<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use Illuminate\Database\Eloquent\Collection;

class AlbumRepository
{
    public function getAlbums(): Collection
    {
        return Album::with(['user'])->get();
    }
}
