<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\Actions;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTagAction
{
    use AsAction;

    public function handle(Tag $tag): Tag
    {
        return $tag;
    }

    public function asController(Tag $tag): JsonResponse
    {
        $tag = $this->handle($tag);

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
