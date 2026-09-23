<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Tasks\ListEpisodesTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListEpisodesAction extends BaseAction
{
    public function __construct(
        private readonly ListEpisodesTask $listEpisodesTask,
    ) {
    }

    public function handle(): Collection
    {
        return $this->listEpisodesTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $episodes = $this->handle();
        $includes = (string) $request->query('include', '');

        return fractal($episodes, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->parseIncludes($includes)
            ->addMeta(['count' => $episodes->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
