<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Models\AlbumDisc;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateOrCreateAlbumDiscTask extends ParentTask
{
    public function run(Album $album, int $number, ?string $name = null): AlbumDisc
    {
        $number = max(1, $number);

        return AlbumDisc::query()->updateOrCreate(
            [
                'album_id' => $album->id,
                'number' => $number,
            ],
            [
                'name' => $name ?: 'CD '.$number,
            ],
        );
    }
}
