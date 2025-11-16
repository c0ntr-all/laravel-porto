<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Data\DTO\TagListDto;
use App\Containers\AppSection\Tag\Data\Repositories\TagRepository;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListTagsAction
{
    use AsAction;

    public function __construct(
        private readonly TagRepository $tagRepository
    )
    {
    }

    public function handle(TagListDto $dto): Collection
    {
        return $this->tagRepository->get();
    }

    public function asController(Tag $tag): JsonResponse
    {
        $dto = TagListDto::from(['user_id' => auth()->user()->id]);

        $tags = $this->handle($dto);

        return fractal($tags, new TagTransformer())
            ->withResourceName('tags')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
