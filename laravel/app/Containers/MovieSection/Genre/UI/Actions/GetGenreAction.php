<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\Actions;

use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetGenreAction extends BaseAction
{
    public function handle(Genre $genre): Genre
    {
        return $genre;
    }

    public function asController(Genre $genre, GetRequest $request): JsonResponse
    {
        $genre = $this->handle($genre);

        return fractal($genre, new GenreTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_GENRE->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
