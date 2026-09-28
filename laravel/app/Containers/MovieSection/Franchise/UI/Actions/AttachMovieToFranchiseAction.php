<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\AttachMovieToFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\AttachMovieRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseTransformer;
use App\Containers\MovieSection\Movie\Tasks\FindMovieByIdTask;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class AttachMovieToFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly AttachMovieToFranchiseTask $attachMovieToFranchiseTask,
        private readonly FindMovieByIdTask $findMovieByIdTask,
    ) {
    }

    public function asController(Franchise $franchise, AttachMovieRequest $request): JsonResponse
    {
        $movie = $this->findMovieByIdTask->run((int) $request->validated('movie_id'));
        $order = $request->validated('order');

        $franchise = $this->attachMovieToFranchiseTask->run(
            $franchise,
            $movie,
            $order !== null ? (int) $order : null,
        );

        return fractal($franchise, new FranchiseTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FRANCHISE->value)
            ->addMeta(['message' => 'Movie added to franchise successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
