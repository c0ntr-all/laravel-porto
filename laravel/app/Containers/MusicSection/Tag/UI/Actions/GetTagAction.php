<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTagAction
{
    use AsAction;

    public function handle(MusicTag $tag): MusicTag
    {
        return $tag;
    }

    public function asController(MusicTag $tag): JsonResponse
    {
        $tag = $this->handle($tag);

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
