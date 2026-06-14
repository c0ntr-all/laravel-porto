<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Data\Repositories\TagRepository;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class ListTagsAction extends BaseAction
{

    public function __construct(
        private readonly TagRepository $tagRepository
    )
    {
    }

    public function handle(): Collection
    {
        return $this->tagRepository->getTagsTree();
    }

    public function asController(MusicTag $tag): JsonResponse
    {
        $tags = $this->handle();

        return fractal($tags, new TagTransformer())
            ->withResourceName('tags')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
