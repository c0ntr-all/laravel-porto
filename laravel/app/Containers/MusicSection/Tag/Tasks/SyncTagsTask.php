<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use InvalidArgumentException;

class SyncTagsTask extends ParentTask
{
    public function __construct(
        private readonly ApplySystemTagsToTracksTask $applySystemTagsToTracksTask,
        private readonly DispatchSystemTagRecalculationTask $dispatchSystemTagRecalculationTask,
    ) {
    }

    /**
     * Tags are stored only on tracks. Album/Artist assignment attaches the tags
     * to every track of that entity, then aggregated counts are recalculated.
     *
     * @return array<string, mixed>
     */
    public function run(Album|Artist|Track $model, SyncTagsDto $dto): array
    {
        $tagIds = array_values(array_unique(array_map('intval', $dto->tags)));

        if ($model instanceof Track) {
            $result = $model->tags()->sync($tagIds);
            $this->dispatchSystemTagRecalculationTask->forTrack($model);

            return $result;
        }

        if ($model instanceof Album) {
            $trackIds = $model->tracks()->pluck('id');
            $this->applySystemTagsToTracksTask->run($trackIds, $tagIds);
            $this->dispatchSystemTagRecalculationTask->forAlbum($model);

            return ['attached' => $tagIds];
        }

        if ($model instanceof Artist) {
            $trackIds = $model->tracks()->pluck('id');
            $this->applySystemTagsToTracksTask->run($trackIds, $tagIds);
            $this->dispatchSystemTagRecalculationTask->forArtist($model);

            return ['attached' => $tagIds];
        }

        throw new InvalidArgumentException('System tags can only be synced for Track, Album or Artist.');
    }
}
