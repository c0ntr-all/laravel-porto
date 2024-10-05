<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\Repositories\TagRepository;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Facades\DB;

class DeleteTagWithChildrenTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function run(MusicTag $tag): int
    {
        try {
            $ids = $this->getAllIds($tag)->toArray();

            return DB::transaction(function() use ($ids) {
                $result = $this->tagRepository->deleteTags($ids);

                if (!$result) {
                    throw new DeleteResourceFailedException();
                }

                return $result;
            });
        } catch (\Exception $e) {
            throw new DeleteResourceFailedException();
        }
    }

    private function getAllIds($tag)
    {
        $ids = collect([$tag->id]);

        if ($tag->tags->isNotEmpty()) {
            $childIds = $tag->tags->flatMap(function ($child) {
                return $this->getAllIds($child);
            });
            $ids = $ids->merge($childIds);
        }

        return $ids;
    }
}
