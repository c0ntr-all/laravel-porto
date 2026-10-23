<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAlbumByIdForUserTask extends ParentTask
{
    public function run(int $albumId, int $userId): Album
    {
        $album = Album::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->find($albumId);

        if (!$album) {
            throw (new ModelNotFoundException())->setModel(Album::class, [$albumId]);
        }

        return $album;
    }
}
