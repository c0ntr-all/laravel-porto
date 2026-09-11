<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\Actions;

use App\Containers\MovieSection\Genre\Tasks\ListGenresTask;
use App\Containers\MovieSection\Genre\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Genre\UI\API\Transformers\GenreTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListGenresAction extends BaseAction
{
    public function __construct(
        private readonly ListGenresTask $listGenresTask,
    ) {
    }

    public function handle(): Collection
    {
        return $this->listGenresTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $genres = $this->handle();

        return fractal($genres, new GenreTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_GENRE->value)
            ->addMeta(['count' => $genres->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
