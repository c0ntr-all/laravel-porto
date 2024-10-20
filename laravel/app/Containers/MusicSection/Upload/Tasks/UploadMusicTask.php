<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Tasks\ListAlbumsByNameTask;
use App\Containers\MusicSection\Album\Tasks\UpdateOrCreateAlbumTask;
use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Tasks\UpdateOrCreateArtistTask;
use App\Containers\MusicSection\Tag\Tasks\ListTagsShortTask;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Tasks\UpdateOrCreateTrackTask;
use App\Containers\MusicSection\Upload\Data\DTO\CreateMusicUploadHistoryDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class UploadMusicTask extends ParentTask
{
    public function __construct(
        private readonly CreateMusicUploadHistoryTask $createMusicUploadHistoryTask,
        private readonly UpdateOrCreateArtistTask $updateOrCreateArtistTask,
        private readonly ListTagsShortTask $listTagsShortTask,
        private readonly ListAlbumsByNameTask $listAlbumsByNameTask,
        private readonly UpdateOrCreateAlbumTask $updateOrCreateAlbumTask,
        private readonly UpdateOrCreateTrackTask $updateOrCreateTrackTask,
    ){
    }

    /**
     * Returns the path to the cover in the folder, if there is one
     *
     * @param array $data
     * @return string|null
     */
    public function run(array $data): ?string
    {
        return DB::transaction(function () use ($data) {
            $existingTags = $this->listTagsShortTask->run();

            foreach ($data as $artistData) {
                $artistDto = CreateArtistDto::from($artistData);
                $artistDto->user_id = auth()->user()->id;
                $artist = $this->updateOrCreateArtistTask->run($artistDto);

                foreach($artistData['albums'] as $albumData) {
                    $albumDto = CreateAlbumDto::from($albumData);
                    $albumDto->user_id = auth()->user()->id;

                    if (isset($albumData['original_album'])) {
                        $originalAlbum = $albumData['original_album'];
                        $existingOriginalAlbum = $this->listAlbumsByNameTask->run($artist, $originalAlbum);

                        if ($existingOriginalAlbum) {
                            $albumDto->parent_id = $existingOriginalAlbum->id;
                        }
                    }

                    $album = $this->updateOrCreateAlbumTask->run($artist, $albumDto);
                    $artist->albums()->syncWithoutDetaching([$album->id]);

                    foreach($albumData['tracks'] as $trackData) {
                        $trackDto = CreateTrackDto::from($trackData);
                        $trackDto->user_id = auth()->user()->id;
                        $trackDto->image = $albumDto->image;

                        $track = $this->updateOrCreateTrackTask->run($album, $trackDto);

                        //todo: Move to tasks and repositories
                        $track->artists()->syncWithoutDetaching([$artist->id]);

                        if ($trackData['genre'] && array_key_exists($trackData['genre'], $existingTags)) {
                            $track->tags()->syncWithoutDetaching($existingTags[$trackData['genre']]);
                            $album->tags()->syncWithoutDetaching($existingTags[$trackData['genre']]);
                            $artist->tags()->syncWithoutDetaching($existingTags[$trackData['genre']]);
                        }
                        //todo: Info to sockets
                    }
                }
            }

            $this->createMusicUploadHistoryTask->run(CreateMusicUploadHistoryDto::from([
                'data' => $data
            ]));

            return ['artists' => collect($data)->pluck('name')];
        });
    }
}
