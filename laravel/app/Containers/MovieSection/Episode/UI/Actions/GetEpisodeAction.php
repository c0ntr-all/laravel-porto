<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetEpisodeAction extends BaseAction
{
    public function handle(Episode $episode): Episode
    {
        return $episode;
    }

    public function asController(Episode $episode, GetRequest $request): JsonResponse
    {
        $episode = $this->handle($episode);
        $includes = (string) $request->query('include', '');

        return fractal($episode, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->parseIncludes($includes)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
