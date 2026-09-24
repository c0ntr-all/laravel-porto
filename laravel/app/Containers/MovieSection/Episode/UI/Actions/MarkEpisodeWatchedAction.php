<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Tasks\MarkEpisodeWatchedTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\WatchRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class MarkEpisodeWatchedAction extends BaseAction
{
    public function __construct(
        private readonly MarkEpisodeWatchedTask $markEpisodeWatchedTask,
    ) {
    }

    public function asController(Episode $episode, WatchRequest $request): JsonResponse
    {
        $this->markEpisodeWatchedTask->run($episode, (int) $request->user()->id);

        $episode->load(['watches' => Episode::constrainWatchesToCurrentUser()]);

        return fractal($episode, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->addMeta(['message' => 'Episode marked as watched!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
