<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Data\DTO\MovieUpdateData;
use App\Containers\MovieSection\Movie\Data\Repositories\MovieRepository;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

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
        $existing = $this->movieRepository->findByKpId($dto->kp_id);

        if ($existing === null) {
            $movie = $this->movieRepository->create(MovieCreateData::from([
                'kp_id' => $dto->kp_id,
                'title' => $dto->title,
                'year' => $dto->year,
                'type' => $dto->type,
                'description' => $dto->description,
                'cover' => $dto->cover ?? $dto->kp_img,
                'kp_rating' => $dto->kp_rating,
                'kp_img' => $dto->kp_img,
            ]));

            return ['movie' => $movie, 'was_created' => true];
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

        if ($existing->cover === null || $existing->cover === '') {
            $update['cover'] = $dto->cover ?? $dto->kp_img;
        }

        $movie = $this->movieRepository->update($existing, MovieUpdateData::from($update));

        return ['movie' => $movie, 'was_created' => false];
    }
}
