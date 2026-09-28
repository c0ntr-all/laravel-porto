<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\UpdateMovieOrderInFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\UpdateMovieOrderRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateMovieOrderInFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly UpdateMovieOrderInFranchiseTask $updateMovieOrderInFranchiseTask,
    ) {
    }

    public function asController(
        Franchise $franchise,
        Movie $movie,
        UpdateMovieOrderRequest $request,
    ): JsonResponse {
        $franchise = $this->updateMovieOrderInFranchiseTask->run(
            $franchise,
            $movie,
            (int) $request->validated('order'),
        );

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Movie order in franchise updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
