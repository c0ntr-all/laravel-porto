<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Data\Repositories\TagGroupRepository;
use App\Containers\MusicSection\Tag\UI\API\Requests\TagGroupIndexRequest;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagGroupTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListTagGroupsAction extends BaseAction
{
    public function __construct(
        private readonly TagGroupRepository $tagGroupRepository
    )
    {
    }

    public function handle(): Collection
    {
        return $this->tagGroupRepository->getGroups();
    }

    public function asController(TagGroupIndexRequest $request): JsonResponse
    {
        $groups = $this->handle();

        return fractal($groups, new TagGroupTransformer())
            ->withResourceName('tag_groups')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
