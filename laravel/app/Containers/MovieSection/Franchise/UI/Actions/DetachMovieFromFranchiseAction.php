<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\DetachMovieFromFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\DetachMovieRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DetachMovieFromFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly DetachMovieFromFranchiseTask $detachMovieFromFranchiseTask,
    ) {
    }

    public function asController(
        Franchise $franchise,
        Movie $movie,
        DetachMovieRequest $request,
    ): JsonResponse {
        $franchise = $this->detachMovieFromFranchiseTask->run($franchise, $movie);

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Movie removed from franchise successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
