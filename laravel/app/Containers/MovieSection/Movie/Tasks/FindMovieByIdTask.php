<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindMovieByIdTask extends ParentTask
{
    public function run(int $movieId): Movie
    {
        $movie = Movie::query()->find($movieId);

        if (!$movie) {
            throw (new ModelNotFoundException())->setModel(Movie::class, [$movieId]);
        }

        return $movie;
    }
}
