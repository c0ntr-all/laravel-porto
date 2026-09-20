<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Data\DTO\MovieUpdateData;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\QueryException;

class UpsertImportedMovieTask extends ParentTask
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    /**
     * @return array{movie: Movie, was_created: bool}
     */
    public function run(ParsedKinopoiskFilmDto $dto): array
    {
        $existing = $this->movieRepository->findByKpId($dto->kp_id, forUpdate: true);

        if ($existing === null) {
            try {
                $movie = $this->movieRepository->create(MovieCreateData::from([
                    'kp_id' => $dto->kp_id,
                    'title' => $dto->title,
                    'year' => $dto->year,
                    'type' => $dto->type,
                    'description' => $dto->description,
                    'short_description' => $dto->short_description,
                    'cover' => $dto->cover ?? $dto->kp_img,
                    'kp_rating' => $dto->kp_rating,
                    'kp_img' => $dto->kp_img,
                ]));

                return ['movie' => $movie, 'was_created' => true];
            } catch (QueryException $exception) {
                if (!$this->isDuplicateKpId($exception)) {
                    throw $exception;
                }

                $existing = $this->movieRepository->findByKpId($dto->kp_id, forUpdate: true);
                if ($existing === null) {
                    throw $exception;
                }
            }
        }

        $update = [
            'title' => $dto->title,
            'year' => $dto->year,
            'type' => $dto->type,
            'kp_rating' => $dto->kp_rating,
            'kp_img' => $dto->kp_img,
        ];

        if ($dto->description !== null && $dto->description !== '') {
            $update['description'] = $dto->description;
        }

        if ($dto->short_description !== null && $dto->short_description !== '') {
            $update['short_description'] = $dto->short_description;
        }

        if ($existing->cover === null || $existing->cover === '') {
            $update['cover'] = $dto->cover ?? $dto->kp_img;
        }

        $movie = $this->movieRepository->update($existing, MovieUpdateData::from($update));

        return ['movie' => $movie, 'was_created' => false];
    }

    private function isDuplicateKpId(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = $exception->errorInfo[1] ?? null;

        return $sqlState === '23000' || $driverCode === 1062;
    }
}
