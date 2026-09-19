<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\Actions;

use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Containers\MovieSection\Import\UI\API\Requests\GetRequest;
use App\Containers\MovieSection\Import\UI\API\Transformers\MovieImportTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetMovieImportAction extends BaseAction
{
    public function handle(MovieImport $import, int $userId): MovieImport
    {
        if ((int) $import->user_id !== $userId) {
            throw new AccessDeniedHttpException();
        }

        return $import->load(['movie.genres', 'movie.countries']);
    }

    public function asController(MovieImport $movieImport, GetRequest $request): JsonResponse
    {
        $import = $this->handle($movieImport, (int) auth()->id());

        return fractal($import, new MovieImportTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_IMPORT->value)
            ->parseIncludes(['movie', 'movie.genres', 'movie.countries'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
