<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Tasks\UnmarkEpisodeWatchedTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\WatchRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UnmarkEpisodeWatchedAction extends BaseAction
{
    public function __construct(
        private readonly UnmarkEpisodeWatchedTask $unmarkEpisodeWatchedTask,
    ) {
    }

    public function asController(Episode $episode, WatchRequest $request): JsonResponse
    {
        $this->unmarkEpisodeWatchedTask->run($episode, (int) $request->user()->id);

        $episode->unsetRelation('watches');
        $episode->load(['watches' => Episode::constrainWatchesToCurrentUser()]);

        return fractal($episode, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->addMeta(['message' => 'Episode watch removed!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
