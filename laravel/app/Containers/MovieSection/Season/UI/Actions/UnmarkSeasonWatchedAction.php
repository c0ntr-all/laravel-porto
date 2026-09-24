<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Tasks\UnmarkSeasonWatchedTask;
use App\Containers\MovieSection\Season\UI\API\Requests\WatchRequest;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UnmarkSeasonWatchedAction extends BaseAction
{
    public function __construct(
        private readonly UnmarkSeasonWatchedTask $unmarkSeasonWatchedTask,
    ) {
    }

    public function asController(Season $season, WatchRequest $request): JsonResponse
    {
        $this->unmarkSeasonWatchedTask->run($season, (int) $request->user()->id);

        $season->unsetRelation('watches');
        $season->load([
            'watches' => Season::constrainWatchesToCurrentUser(),
            'episodes.watches' => Episode::constrainWatchesToCurrentUser(),
        ]);

        return fractal($season, new SeasonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_SEASON->value)
            ->parseIncludes(['episodes'])
            ->addMeta([
                'message' => 'Season watch removed! Episode watches will be cleared in background.',
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
