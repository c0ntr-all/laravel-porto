<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\Actions;

use App\Containers\MovieSection\Genre\Data\DTO\GenreCreateData;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\Tasks\CreateGenreTask;
use App\Containers\MovieSection\Genre\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateGenreAction extends BaseAction
{
    public function __construct(
        private readonly CreateGenreTask $createGenreTask,
    ) {
    }

    public function handle(GenreCreateData $dto): Genre
    {
        return $this->createGenreTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $genre = $this->handle(GenreCreateData::from($request->validated()));

        return fractal($genre, new GenreTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_GENRE->value)
            ->addMeta(['message' => 'Genre created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
