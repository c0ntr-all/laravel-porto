<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetMovieAction extends BaseAction
{
    public function handle(Movie $movie): Movie
    {
        return $movie->load(['genres', 'countries']);
    }

    public function asController(Movie $movie, GetRequest $request): JsonResponse
    {
        $movie = $this->handle($movie);

        return fractal($movie, new MovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->parseIncludes(['genres', 'countries'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
