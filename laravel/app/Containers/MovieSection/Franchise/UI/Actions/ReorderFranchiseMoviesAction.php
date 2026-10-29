<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Data\DTO\ReorderFranchiseMoviesData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\ReorderFranchiseMoviesTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\ReorderFranchiseMoviesRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ReorderFranchiseMoviesAction extends BaseAction
{
    public function __construct(
        private readonly ReorderFranchiseMoviesTask $reorderFranchiseMoviesTask,
    ) {
    }

    public function handle(Franchise $franchise, ReorderFranchiseMoviesData $dto): Franchise
    {
        return $this->reorderFranchiseMoviesTask->run($franchise, $dto);
    }

    public function asController(
        Franchise $franchise,
        ReorderFranchiseMoviesRequest $request,
    ): JsonResponse {
        $franchise = $this->handle(
            $franchise,
            ReorderFranchiseMoviesData::from($request->validated()),
        );

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Franchise movies reordered successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
