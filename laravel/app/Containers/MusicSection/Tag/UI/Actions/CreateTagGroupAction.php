<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Data\DTO\TagGroupCreateData;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Containers\MusicSection\Tag\Tasks\CreateTagGroupTask;
use App\Containers\MusicSection\Tag\UI\API\Requests\TagGroupCreateRequest;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagGroupTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateTagGroupAction extends BaseAction
{
    public function __construct(
        private readonly CreateTagGroupTask $createTagGroupTask
    )
    {
    }

    public function handle(TagGroupCreateData $dto): MusicTagGroup
    {
        return $this->createTagGroupTask->run($dto);
    }

    public function asController(TagGroupCreateRequest $request): JsonResponse
    {
        $group = $this->handle(TagGroupCreateData::from($request->validated()));

        return fractal($group, new TagGroupTransformer())
            ->withResourceName('tag_groups')
            ->addMeta(['message' => 'Tag group created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
