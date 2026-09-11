<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\CreateMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncCountriesForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncGenresForMovieTask;
use App\Containers\MovieSection\Movie\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateMovieAction extends BaseAction
{
    public function __construct(
        private readonly CreateMovieTask $createMovieTask,
        private readonly SyncGenresForMovieTask $syncGenresForMovieTask,
        private readonly SyncCountriesForMovieTask $syncCountriesForMovieTask,
    ) {
    }

    public function handle(array $requestData): Movie
    {
        return DB::transaction(function () use ($requestData) {
            $movie = $this->createMovieTask->run(
                MovieCreateData::from(collect($requestData)->except(['genre_ids', 'country_ids'])->all()),
            );

            if (array_key_exists('genre_ids', $requestData)) {
                $this->syncGenresForMovieTask->run($movie, $requestData['genre_ids']);
            }

            if (array_key_exists('country_ids', $requestData)) {
                $this->syncCountriesForMovieTask->run($movie, $requestData['country_ids']);
            }

            return $movie->load(['genres', 'countries']);
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $movie = $this->handle($request->validated());

        return fractal($movie, new MovieTransformer())
            ->parseIncludes(['genres', 'countries'])
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->addMeta(['message' => 'Movie created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
