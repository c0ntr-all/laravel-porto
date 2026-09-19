<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\Actions;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\MovieImportCreateData;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Containers\MovieSection\Import\Support\KinopoiskApiUrl;
use App\Containers\MovieSection\Import\Support\MovieImportLogMeta;
use App\Containers\MovieSection\Import\Tasks\CreateMovieImportLogTask;
use App\Containers\MovieSection\Import\Tasks\FetchKinopoiskMovieTask;
use App\Containers\MovieSection\Import\Tasks\FinalizeMovieImportLogTask;
use App\Containers\MovieSection\Import\Tasks\FindOrCreateCountriesFromParsedTask;
use App\Containers\MovieSection\Import\Tasks\FindOrCreateGenresFromParsedTask;
use App\Containers\MovieSection\Import\Tasks\MapKinopoiskMovieTask;
use App\Containers\MovieSection\Import\Tasks\UpsertImportedMovieTask;
use App\Containers\MovieSection\Import\UI\API\Requests\ImportRequest;
use App\Containers\MovieSection\Import\UI\API\Transformers\MovieImportTransformer;
use App\Containers\MovieSection\Movie\Tasks\SyncCountriesForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncGenresForMovieTask;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportMovieFromKinopoiskAction extends BaseAction
{
    public function __construct(
        private readonly CreateMovieImportLogTask $createMovieImportLogTask,
        private readonly FetchKinopoiskMovieTask $fetchKinopoiskMovieTask,
        private readonly MapKinopoiskMovieTask $mapKinopoiskMovieTask,
        private readonly FindOrCreateGenresFromParsedTask $findOrCreateGenresFromParsedTask,
        private readonly FindOrCreateCountriesFromParsedTask $findOrCreateCountriesFromParsedTask,
        private readonly UpsertImportedMovieTask $upsertImportedMovieTask,
        private readonly SyncGenresForMovieTask $syncGenresForMovieTask,
        private readonly SyncCountriesForMovieTask $syncCountriesForMovieTask,
        private readonly FinalizeMovieImportLogTask $finalizeMovieImportLogTask,
    ) {
    }

    public function handle(int $kpId, int $userId): MovieImport
    {
        $import = $this->createMovieImportLogTask->run(MovieImportCreateData::from([
            'user_id' => $userId,
            'kp_id' => $kpId,
            'source_url' => KinopoiskApiUrl::movie($kpId),
        ]));
        $startedAtNs = hrtime(true);
        $response = null;
        $parsed = null;

        try {
            $response = $this->fetchKinopoiskMovieTask->run($kpId);
            $parsed = $this->mapKinopoiskMovieTask->run($response);

            $result = DB::transaction(function () use ($parsed) {
                $genres = $this->findOrCreateGenresFromParsedTask->run($parsed->genres);
                $countries = $this->findOrCreateCountriesFromParsedTask->run($parsed->countries);
                $upserted = $this->upsertImportedMovieTask->run($parsed);

                $this->syncGenresForMovieTask->run(
                    $upserted['movie'],
                    array_map(static fn ($genre) => (int) $genre->id, $genres),
                );
                $this->syncCountriesForMovieTask->run(
                    $upserted['movie'],
                    array_map(static fn ($country) => (int) $country->id, $countries),
                );

                return $upserted;
            });

            $movie = $result['movie']->load(['genres', 'countries']);

            return $this->finalizeMovieImportLogTask->run(
                import: $import,
                status: MovieImportStatusEnum::Completed,
                startedAtNs: $startedAtNs,
                movieId: (int) $movie->id,
                wasCreated: $result['was_created'],
                httpStatus: $response->http_status,
                parsedPayload: $parsed->toArray(),
                meta: MovieImportLogMeta::from($response, $parsed, null, 201),
            )->load(['movie.genres', 'movie.countries']);
        } catch (KinopoiskImportException $exception) {
            $failed = $this->finalizeMovieImportLogTask->run(
                import: $import,
                status: MovieImportStatusEnum::Failed,
                startedAtNs: $startedAtNs,
                httpStatus: $this->kinopoiskHttpStatus($response, $exception),
                errorMessage: $exception->getMessage(),
                meta: MovieImportLogMeta::from($response, $parsed, $exception, $exception->httpStatus),
            );

            throw $exception->withImport($failed);
        } catch (Throwable $exception) {
            $failed = $this->finalizeMovieImportLogTask->run(
                import: $import,
                status: MovieImportStatusEnum::Failed,
                startedAtNs: $startedAtNs,
                httpStatus: $response?->http_status,
                errorMessage: $exception->getMessage(),
                meta: MovieImportLogMeta::from($response, $parsed, $exception, 500),
            );

            throw (new KinopoiskImportException(
                'Failed to import film from PoiskKino API.',
                500,
                $failed,
                $exception,
                $failed->meta ?? [],
            ));
        }
    }

    public function asController(ImportRequest $request): JsonResponse
    {
        try {
            $import = $this->handle((int) $request->validated('kp_id'), (int) auth()->id());
            $status = 201;
            $message = $import->was_created ? 'Movie imported successfully!' : 'Movie updated from Kinopoisk.';
        } catch (KinopoiskImportException $exception) {
            $import = $exception->import ?? throw $exception;
            $status = $exception->httpStatus;
            $message = $exception->getMessage();
        }

        return fractal($import, new MovieImportTransformer())
            ->parseIncludes(['movie', 'movie.genres', 'movie.countries'])
            ->withResourceName(ContainerAliasEnum::MOVIE_IMPORT->value)
            ->addMeta(['message' => $message])
            ->respond($status, [], JSON_PRETTY_PRINT);
    }

    private function kinopoiskHttpStatus(?KinopoiskApiResponseDto $response, KinopoiskImportException $exception): ?int
    {
        if ($response !== null) {
            return $response->http_status;
        }

        $status = $exception->context['kinopoisk']['http_status'] ?? null;

        return is_int($status) ? $status : null;
    }
}
