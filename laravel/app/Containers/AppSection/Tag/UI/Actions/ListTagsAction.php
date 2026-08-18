<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Data\DTO\TagListDto;
use App\Containers\AppSection\Tag\Data\Repositories\TagRepository;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListTagsAction extends BaseAction
{
    public function __construct(
        private readonly TagRepository $tagRepository
    )
    {
    }

    public function handle(TagListDto $dto): Collection
    {
        return $this->tagRepository->get($dto->user_id);
    }

    public function asController(): JsonResponse
    {
        $dto = TagListDto::from(['user_id' => auth()->id()]);

        $tags = $this->handle($dto);

        return fractal($tags, new TagTransformer())
            ->withResourceName('tags')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
