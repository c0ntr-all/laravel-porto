<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\Actions;

use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use App\Containers\MovieSection\Import\Support\KinopoiskApiUrl;
use App\Containers\MovieSection\Import\Tasks\FetchKinopoiskSeasonsTask;
use App\Containers\MovieSection\Import\Tasks\MapKinopoiskSeasonsTask;
use App\Containers\MovieSection\Import\Tasks\UpsertImportedSeasonsTask;
use App\Containers\MovieSection\Import\UI\API\Requests\ImportSeasonsRequest;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ImportSeasonsFromKinopoiskAction extends BaseAction
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly FetchKinopoiskSeasonsTask $fetchKinopoiskSeasonsTask,
        private readonly MapKinopoiskSeasonsTask $mapKinopoiskSeasonsTask,
        private readonly UpsertImportedSeasonsTask $upsertImportedSeasonsTask,
    ) {
    }

    /**
     * @return array{movie: Movie, stats: array<string, int>, source_url: string}
     */
    public function handle(int $movieKpId): array
    {
        $movie = $this->movieRepository->findByKpId($movieKpId);
        if ($movie === null) {
            throw new NotFoundHttpException('Movie with this Kinopoisk id was not found. Import the series first.');
        }

        $response = $this->fetchKinopoiskSeasonsTask->run($movieKpId);
        $parsed = $this->mapKinopoiskSeasonsTask->run($response);

        $stats = DB::transaction(function () use ($movie, $parsed) {
            $locked = $this->movieRepository->findByKpId((int) $movie->kp_id, forUpdate: true);
            if ($locked === null) {
                throw new NotFoundHttpException('Movie with this Kinopoisk id was not found. Import the series first.');
            }

            return $this->upsertImportedSeasonsTask->run($locked, $parsed);
        });

        $movie = $movie->fresh(['seasons.episodes', 'genres', 'countries']);

        return [
            'movie' => $movie,
            'stats' => $stats,
            'source_url' => $response->url !== '' ? $response->url : KinopoiskApiUrl::season([
                'movieId' => $movieKpId,
            ]),
        ];
    }

    public function asController(ImportSeasonsRequest $request): JsonResponse
    {
        try {
            $result = $this->handle((int) $request->validated('kp_id'));
        } catch (KinopoiskImportException $exception) {
            return response()->json([
                'meta' => [
                    'message' => $exception->getMessage(),
                    'context' => $exception->context,
                ],
            ], $exception->httpStatus);
        } catch (Throwable $exception) {
            if ($exception instanceof NotFoundHttpException) {
                throw $exception;
            }

            throw new KinopoiskImportException(
                'Failed to import seasons from PoiskKino API.',
                500,
                null,
                $exception,
            );
        }

        return fractal($result['movie'], new MovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->parseIncludes(['seasons.episodes', 'genres', 'countries'])
            ->addMeta([
                'message' => 'Seasons imported successfully!',
                'source_url' => $result['source_url'],
                'seasons_total' => $result['stats']['seasons_total'],
                'seasons_created' => $result['stats']['seasons_created'],
                'seasons_updated' => $result['stats']['seasons_updated'],
                'episodes_total' => $result['stats']['episodes_total'],
                'episodes_created' => $result['stats']['episodes_created'],
                'episodes_updated' => $result['stats']['episodes_updated'],
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
