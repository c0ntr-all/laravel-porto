<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Data\DTO\TagUpdateData;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Tasks\UpdateTagTask;
use App\Containers\MusicSection\Tag\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateTagAction extends BaseAction
{
    public function __construct(
        private readonly UpdateTagTask $updateTagTask
    )
    {
    }

    public function handle(MusicTag $tag, TagUpdateData $dto): MusicTag
    {
        return $this->updateTagTask->run($tag, $dto);
    }

    public function asController(MusicTag $tag, UpdateRequest $request): JsonResponse
    {
        $tag = $this->handle($tag, TagUpdateData::from($request->validated()));

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->addMeta(['message' => 'Tag updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
