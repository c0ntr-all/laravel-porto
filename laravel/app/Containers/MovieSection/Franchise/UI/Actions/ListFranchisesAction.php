<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Tasks\ListFranchisesTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListFranchisesAction extends BaseAction
{
    public function __construct(
        private readonly ListFranchisesTask $listFranchisesTask,
    ) {
    }

    public function handle(): Collection
    {
        return $this->listFranchisesTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $franchises = $this->handle();

        return fractal($franchises, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['count' => $franchises->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
