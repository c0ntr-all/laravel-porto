<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Data\DTO\TagGroupUpdateData;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Containers\MusicSection\Tag\Tasks\UpdateTagGroupTask;
use App\Containers\MusicSection\Tag\UI\API\Requests\TagGroupUpdateRequest;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagGroupTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateTagGroupAction extends BaseAction
{
    public function __construct(
        private readonly UpdateTagGroupTask $updateTagGroupTask
    )
    {
    }

    public function handle(MusicTagGroup $tagGroup, TagGroupUpdateData $dto): MusicTagGroup
    {
        return $this->updateTagGroupTask->run($tagGroup, $dto);
    }

    public function asController(MusicTagGroup $tagGroup, TagGroupUpdateRequest $request): JsonResponse
    {
        $group = $this->handle($tagGroup, TagGroupUpdateData::from($request->validated()));

        return fractal($group, new TagGroupTransformer())
            ->withResourceName('tag_groups')
            ->addMeta(['message' => 'Tag group updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
