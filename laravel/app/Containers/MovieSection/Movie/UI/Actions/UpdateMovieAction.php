<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Data\DTO\MovieUpdateData;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\SyncCountriesForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncGenresForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\UpdateMovieTask;
use App\Containers\MovieSection\Movie\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateMovieAction extends BaseAction
{
    public function __construct(
        private readonly UpdateMovieTask $updateMovieTask,
        private readonly SyncGenresForMovieTask $syncGenresForMovieTask,
        private readonly SyncCountriesForMovieTask $syncCountriesForMovieTask,
    ) {
    }

    public function handle(Movie $movie, array $requestData): Movie
    {
        return DB::transaction(function () use ($movie, $requestData) {
            $movie = $this->updateMovieTask->run(
                $movie,
                MovieUpdateData::from(collect($requestData)->except(['genre_ids', 'country_ids'])->all()),
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

    public function asController(Movie $movie, UpdateRequest $request): JsonResponse
    {
        $movie = $this->handle($movie, $request->validated());

        return fractal($movie, new MovieTransformer())
            ->parseIncludes(['genres', 'countries'])
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->addMeta(['message' => 'Movie updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
