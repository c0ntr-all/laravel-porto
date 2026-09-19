<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\MovieSection\Genre\Data\Repositories\GenreRepository;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Import\Data\DTO\ParsedGenreDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindOrCreateGenresFromParsedTask extends ParentTask
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
    ) {
    }

    /**
     * @param list<ParsedGenreDto> $genres
     * @return list<Genre>
     */
    public function run(array $genres): array
    {
        $models = [];

        foreach ($genres as $genre) {
            $name = trim($genre->name);
            if ($name === '') {
                continue;
            }

            $models[] = $this->genreRepository->firstOrCreateByName($name, $genre->kp_id);
        }

        return $models;
    }
}
