<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Tasks;

use App\Containers\MovieSection\Genre\Data\DTO\GenreCreateData;
use App\Containers\MovieSection\Genre\Data\Repositories\GenreRepository;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateGenreTask extends ParentTask
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
    ) {
    }

    public function run(GenreCreateData $dto): Genre
    {
        return $this->genreRepository->create($dto);
    }
}
