<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Data\DTO\SyncUserTagsDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\Tasks\Task as ParentTask;
use InvalidArgumentException;

class SyncUserTagsTask extends ParentTask
{
    public function __construct(
        private readonly ApplyUserTagsToTracksTask $applyUserTagsToTracksTask,
        private readonly DispatchUserTagRecalculationTask $dispatchUserTagRecalculationTask,
    ) {
    }

    /**
     * User tags are stored only on tracks. Album/Artist assignment attaches
     * the tags to every track of that entity, then per-user counts are recalculated.
     *
     * @return array<string, mixed>
     */
    public function run(Album|Artist|Track $model, SyncUserTagsDto $dto): array
    {
        $tagIds = array_values(array_unique(array_map('intval', $dto->tags)));

        if ($model instanceof Track) {
            $current = $model->userTags()
                ->wherePivot('user_id', $dto->user_id)
                ->pluck('music_user_tags.id')
                ->all();

            $detach = array_values(array_diff($current, $tagIds));
            if ($detach !== []) {
                $model->userTags()->wherePivot('user_id', $dto->user_id)->detach($detach);
            }

            $this->applyUserTagsToTracksTask->run($dto->user_id, [$model->id], $tagIds);
            $this->dispatchUserTagRecalculationTask->forTrack($dto->user_id, $model);

            return ['attached' => array_values(array_diff($tagIds, $current)), 'detached' => $detach];
        }

        if ($model instanceof Album) {
            $trackIds = $model->tracks()->pluck('id');
            $this->applyUserTagsToTracksTask->run($dto->user_id, $trackIds, $tagIds);
            $this->dispatchUserTagRecalculationTask->forAlbum($dto->user_id, $model);

            return ['attached' => $tagIds];
        }

        if ($model instanceof Artist) {
            $trackIds = $model->tracks()->pluck('id');
            $this->applyUserTagsToTracksTask->run($dto->user_id, $trackIds, $tagIds);
            $this->dispatchUserTagRecalculationTask->forArtist($dto->user_id, $model);

            return ['attached' => $tagIds];
        }

        throw new InvalidArgumentException('User tags can only be synced for Track, Album or Artist.');
    }
}
