<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateDto;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Tasks\CreateTagTask;
use App\Containers\AppSection\Tag\UI\API\Requests\CreateRequest;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class CreateTagAction extends BaseAction
{

    public function __construct(
        private readonly CreateTagTask $createTagTask
    )
    {
    }

    public function handle(TagCreateDto $dto): Tag
    {
        return $this->createTagTask->run($dto);
    }

    public function asController(Tag $tag, CreateRequest $request): JsonResponse
    {
        $dto = TagCreateDto::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $tag = $this->handle($dto);

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->addMeta(['message' => 'Tag created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
