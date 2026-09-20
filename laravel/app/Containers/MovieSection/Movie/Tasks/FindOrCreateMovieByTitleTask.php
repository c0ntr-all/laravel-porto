<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindOrCreateMovieByTitleTask extends ParentTask
{
    public function __construct(
        private readonly CreateMovieTask $createMovieTask,
    ) {
    }

    public function run(string $title, ?MovieTypeEnum $type = null): Movie
    {
        $normalizedTitle = trim($title);

        $existing = Movie::query()
            ->where('title', $normalizedTitle)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createMovieTask->run(MovieCreateData::from([
            'title' => $normalizedTitle,
            'type' => $type ?? MovieTypeEnum::MOVIE,
        ]));
    }
}
