<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\Actions;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\DeleteMovieTask;
use App\Containers\MovieSection\Movie\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteMovieAction extends BaseAction
{
    public function __construct(
        private readonly DeleteMovieTask $deleteMovieTask,
    ) {
    }

    public function handle(Movie $movie): bool
    {
        return $this->deleteMovieTask->run($movie);
    }

    public function asController(Movie $movie, DeleteRequest $request): JsonResponse
    {
        $this->handle($movie);

        return response()->json([
            'meta' => [
                'message' => 'Movie successfully deleted!',
            ],
        ]);
    }
}
