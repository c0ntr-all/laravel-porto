<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\Actions;

use App\Containers\MovieSection\Genre\Data\DTO\GenreUpdateData;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\Tasks\UpdateGenreTask;
use App\Containers\MovieSection\Genre\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateGenreAction extends BaseAction
{
    public function __construct(
        private readonly UpdateGenreTask $updateGenreTask,
    ) {
    }

    public function handle(Genre $genre, GenreUpdateData $dto): Genre
    {
        return $this->updateGenreTask->run($genre, $dto);
    }

    public function asController(Genre $genre, UpdateRequest $request): JsonResponse
    {
        $genre = $this->handle($genre, GenreUpdateData::from($request->validated()));

        return fractal($genre, new GenreTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_GENRE->value)
            ->addMeta(['message' => 'Genre updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
