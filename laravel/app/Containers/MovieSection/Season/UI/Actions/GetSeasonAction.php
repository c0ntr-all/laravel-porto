<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetSeasonAction extends BaseAction
{
    public function handle(Season $season): Season
    {
        return $season->load(['episodes']);
    }

    public function asController(Season $season, GetRequest $request): JsonResponse
    {
        $season = $this->handle($season);
        $includes = (string) $request->query('include', 'episodes');

        return fractal($season, new SeasonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_SEASON->value)
            ->parseIncludes($includes)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
