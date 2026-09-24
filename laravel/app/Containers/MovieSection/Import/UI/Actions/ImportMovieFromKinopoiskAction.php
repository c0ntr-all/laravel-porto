<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\Actions;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\MovieImportCreateData;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Containers\MovieSection\Import\Support\KinopoiskApiUrl;
use App\Containers\MovieSection\Import\Support\MovieImportLogMeta;
use App\Containers\MovieSection\Import\Support\MovieStateSnapshot;
use App\Containers\MovieSection\Import\Tasks\CreateMovieImportLogTask;
use App\Containers\MovieSection\Import\Tasks\FetchKinopoiskMovieTask;
use App\Containers\MovieSection\Import\Tasks\FetchKinopoiskSeasonsTask;
use App\Containers\MovieSection\Import\Tasks\FinalizeMovieImportLogTask;
use App\Containers\MovieSection\Import\Tasks\FindOrCreateCountriesFromParsedTask;
use App\Containers\MovieSection\Import\Tasks\FindOrCreateGenresFromParsedTask;
use App\Containers\MovieSection\Import\Tasks\FindOrCreatePersonsFromParsedTask;
use App\Containers\MovieSection\Import\Tasks\MapKinopoiskMovieTask;
use App\Containers\MovieSection\Import\Tasks\MapKinopoiskSeasonsTask;
use App\Containers\MovieSection\Import\Tasks\UpsertImportedMovieTask;
use App\Containers\MovieSection\Import\Tasks\UpsertImportedSeasonsTask;
use App\Containers\MovieSection\Import\UI\API\Requests\ImportRequest;
use App\Containers\MovieSection\Import\UI\API\Transformers\MovieImportTransformer;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Tasks\SyncCountriesForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncGenresForMovieTask;
use App\Containers\MovieSection\Movie\Tasks\SyncPersonsForMovieTask;
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
        private readonly FetchKinopoiskSeasonsTask $fetchKinopoiskSeasonsTask,
        private readonly MapKinopoiskSeasonsTask $mapKinopoiskSeasonsTask,
        private readonly FindOrCreateGenresFromParsedTask $findOrCreateGenresFromParsedTask,
        private readonly FindOrCreateCountriesFromParsedTask $findOrCreateCountriesFromParsedTask,
        private readonly FindOrCreatePersonsFromParsedTask $findOrCreatePersonsFromParsedTask,
        private readonly UpsertImportedMovieTask $upsertImportedMovieTask,
        private readonly UpsertImportedSeasonsTask $upsertImportedSeasonsTask,
        private readonly SyncGenresForMovieTask $syncGenresForMovieTask,
        private readonly SyncCountriesForMovieTask $syncCountriesForMovieTask,
        private readonly SyncPersonsForMovieTask $syncPersonsForMovieTask,
        private readonly FinalizeMovieImportLogTask $finalizeMovieImportLogTask,
        private readonly MovieRepository $movieRepository,
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

            $parsedSeasons = [];
            $seasonsSourceUrl = null;
            if ($this->shouldImportSeasons($parsed->type)) {
                $seasonsResponse = $this->fetchKinopoiskSeasonsTask->run($parsed->kp_id);
                $parsedSeasons = $this->mapKinopoiskSeasonsTask->run($seasonsResponse);
                $seasonsSourceUrl = $seasonsResponse->url;
            }

            $result = DB::transaction(function () use ($parsed, $parsedSeasons) {
                $existing = $this->movieRepository->findByKpId($parsed->kp_id, forUpdate: true);
                $before = $existing !== null ? MovieStateSnapshot::fromMovie($existing) : null;

                $genres = $this->findOrCreateGenresFromParsedTask->run($parsed->genres);
                $countries = $this->findOrCreateCountriesFromParsedTask->run($parsed->countries);
                $personRows = $this->findOrCreatePersonsFromParsedTask->run($parsed->persons);
                $upserted = $this->upsertImportedMovieTask->run($parsed);

                $this->syncGenresForMovieTask->run(
                    $upserted['movie'],
                    array_map(static fn ($genre) => (int) $genre->id, $genres),
                );
                $this->syncCountriesForMovieTask->run(
                    $upserted['movie'],
                    array_map(static fn ($country) => (int) $country->id, $countries),
                );
                $this->syncPersonsForMovieTask->run($upserted['movie'], $personRows);

                $seasonsStats = null;
                if ($parsedSeasons !== []) {
                    $seasonsStats = $this->upsertImportedSeasonsTask->run($upserted['movie'], $parsedSeasons);
                }

                $relations = ['genres', 'countries', 'persons'];
                if ($this->shouldImportSeasons($parsed->type)) {
                    $relations[] = 'seasons.episodes';
                }

                $movie = $upserted['movie']->load($relations);
                $after = MovieStateSnapshot::fromMovie($movie);

                return [
                    'movie' => $movie,
                    'was_created' => $upserted['was_created'],
                    'before' => $before,
                    'after' => $after,
                    'changes' => MovieStateSnapshot::diff($before, $after),
                    'seasons_stats' => $seasonsStats,
                ];
            });

            $movie = $result['movie'];
            $meta = MovieImportLogMeta::from(
                $response,
                $parsed,
                null,
                201,
                $result['before'],
                $result['after'],
                $result['changes'],
                $result['was_created'],
            );

            if ($result['seasons_stats'] !== null || $seasonsSourceUrl !== null) {
                $meta['seasons'] = array_filter([
                    'source_url' => $seasonsSourceUrl,
                    ...(is_array($result['seasons_stats']) ? $result['seasons_stats'] : [
                        'seasons_total' => 0,
                        'seasons_created' => 0,
                        'seasons_updated' => 0,
                        'episodes_total' => 0,
                        'episodes_created' => 0,
                        'episodes_updated' => 0,
                    ]),
                ], static fn (mixed $value) => $value !== null);
            }

            $includes = ['movie.genres', 'movie.countries', 'movie.persons'];
            if ($this->shouldImportSeasons($parsed->type)) {
                $includes[] = 'movie.seasons.episodes';
            }

            return $this->finalizeMovieImportLogTask->run(
                import: $import,
                status: MovieImportStatusEnum::Completed,
                startedAtNs: $startedAtNs,
                movieId: (int) $movie->id,
                wasCreated: $result['was_created'],
                httpStatus: $response->http_status,
                parsedPayload: $parsed->toArray(),
                meta: $meta,
            )->load($includes);
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
            ->parseIncludes($this->responseIncludes($import))
            ->withResourceName(ContainerAliasEnum::MOVIE_IMPORT->value)
            ->addMeta(['message' => $message])
            ->respond($status, [], JSON_PRETTY_PRINT);
    }

    private function shouldImportSeasons(MovieTypeEnum $type): bool
    {
        return $type === MovieTypeEnum::TV_SERIES || $type === MovieTypeEnum::SHOW;
    }

    /**
     * @return list<string>
     */
    private function responseIncludes(MovieImport $import): array
    {
        $includes = ['movie', 'movie.genres', 'movie.countries', 'movie.persons'];

        $type = $import->movie?->type;
        if ($type instanceof MovieTypeEnum && $this->shouldImportSeasons($type)) {
            $includes[] = 'movie.seasons.episodes';
        }

        return $includes;
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
