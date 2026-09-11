<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Tasks;

use App\Containers\MovieSection\Genre\Data\Repositories\GenreRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListGenresTask extends ParentTask
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
    ) {
    }

    public function run(): Collection
    {
        return $this->genreRepository->get();
    }
}
