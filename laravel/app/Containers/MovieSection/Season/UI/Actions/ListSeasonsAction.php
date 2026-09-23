<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Season\Tasks\ListSeasonsTask;
use App\Containers\MovieSection\Season\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListSeasonsAction extends BaseAction
{
    public function __construct(
        private readonly ListSeasonsTask $listSeasonsTask,
    ) {
    }

    public function handle(): Collection
    {
        return $this->listSeasonsTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $seasons = $this->handle();
        $includes = (string) $request->query('include', '');

        return fractal($seasons, new SeasonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_SEASON->value)
            ->parseIncludes($includes)
            ->addMeta(['count' => $seasons->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
