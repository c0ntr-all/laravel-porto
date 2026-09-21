<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\ListMovieCreditsTask;
use App\Containers\MovieSection\Movie\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieCreditTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ListMovieCreditsAction extends BaseAction
{
    public function __construct(
        private readonly ListMovieCreditsTask $listMovieCreditsTask,
    ) {
    }

    public function handle(Movie $movie)
    {
        return $this->listMovieCreditsTask->run($movie);
    }

    public function asController(Movie $movie, GetRequest $request): JsonResponse
    {
        $credits = $this->handle($movie);

        return fractal($credits, new MovieCreditTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_CREDIT->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
