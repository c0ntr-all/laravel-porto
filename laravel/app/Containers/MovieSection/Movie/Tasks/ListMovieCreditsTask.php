<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListMovieCreditsTask extends ParentTask
{
    /**
     * @return Collection<int, \App\Containers\MovieSection\Movie\Models\MoviePersonCredit>
     */
    public function run(Movie $movie): Collection
    {
        return $movie->credits()
            ->with(['person', 'profession'])
            ->orderBy('id')
            ->get()
            ->filter(static fn ($credit) => $credit->person !== null && $credit->profession !== null)
            ->values();
    }
}
