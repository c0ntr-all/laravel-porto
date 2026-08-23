<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\Actions;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Tag\UI\API\Transformers\TagTransformer;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class GetTagAction extends BaseAction
{

    public function handle(MusicTag $tag): MusicTag
    {
        return $tag;
    }

    public function asController(MusicTag $tag, GetRequest $request): JsonResponse
    {
        $tag = $this->handle($tag);

        return fractal($tag, new TagTransformer())
            ->withResourceName('tags')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
