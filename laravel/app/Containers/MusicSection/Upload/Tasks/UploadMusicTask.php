<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Tasks\ListAlbumsByNameTask;
use App\Containers\MusicSection\Album\Tasks\UpdateOrCreateAlbumTask;
use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Tasks\SyncAlbumsForArtistTask;
use App\Containers\MusicSection\Artist\Tasks\UpdateOrCreateArtistTask;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\ListTagsShortTask;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Tasks\SyncArtistsForTrackTask;
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
        private readonly SyncAlbumsForArtistTask $syncAlbumsForArtistTask,
        private readonly SyncArtistsForTrackTask $syncArtistsForTrackTask,
        private readonly SyncTagsTask $syncTagsTask
    ){
    }

    /**
     * Returns the path to the cover in the folder, if there is one
     *
     * @param array $data
     * @return array|null
     */
    public function run(array $data): ?array
    {
        return $this->saveData($data);
    }

    private function saveData(array $data)
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

                    $this->syncAlbumsForArtistTask->run($artist, [$album->id]);

                    foreach($albumData['tracks'] as $trackData) {
                        $trackDto = CreateTrackDto::from($trackData);
                        $trackDto->user_id = auth()->user()->id;
                        $trackDto->image = $albumDto->image;

                        $track = $this->updateOrCreateTrackTask->run($album, $trackDto);

                        $this->syncArtistsForTrackTask->run($track, [$artist->id]);

                        if ($trackData['genre'] && array_key_exists($trackData['genre'], $existingTags)) {
                            $tagsForSync = ['tags' => [$existingTags[$trackData['genre']]]];

                            $this->syncTagsTask->run($track, SyncTagsDto::from($tagsForSync));
                            $this->syncTagsTask->run($album, SyncTagsDto::from($tagsForSync));
                            $this->syncTagsTask->run($artist, SyncTagsDto::from($tagsForSync));
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
