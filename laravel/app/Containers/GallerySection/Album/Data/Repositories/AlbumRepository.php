<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use Illuminate\Database\Eloquent\Collection;

class AlbumRepository
{
    /**
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Album::with(['user'])->get();
    }

    /**
     * @param string $systemCode
     * @return Album
     */
    public function getSystemAlbumByCode(string $systemCode): Album
    {
        return Album::withoutGlobalScopes()->where('system_code', $systemCode)->first();
    }
}
