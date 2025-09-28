<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateData;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Tasks\CreateTagTask;
use App\Containers\AppSection\Tag\UI\API\Requests\CreateRequest;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTagAction
{
    use AsAction;

    public function __construct(
        private readonly CreateTagTask $createTagTask
    )
    {
    }

    public function handle(TagCreateData $dto): Tag
    {
        return $this->createTagTask->run($dto);
    }

    public function asController(Tag $tag, CreateRequest $request): JsonResponse
    {
        $dto = TagCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $tag = $this->handle($dto);

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->addMeta(['message' => 'Tag created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
