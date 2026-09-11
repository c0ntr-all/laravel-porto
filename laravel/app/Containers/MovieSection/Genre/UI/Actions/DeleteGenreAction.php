<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\Actions;

use App\Containers\MovieSection\Genre\Models\Genre;
use App\Containers\MovieSection\Genre\Tasks\DeleteGenreTask;
use App\Containers\MovieSection\Genre\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteGenreAction extends BaseAction
{
    public function __construct(
        private readonly DeleteGenreTask $deleteGenreTask,
    ) {
    }

    public function handle(Genre $genre): bool
    {
        return $this->deleteGenreTask->run($genre);
    }

    public function asController(Genre $genre, DeleteRequest $request): JsonResponse
    {
        $this->handle($genre);

        return response()->json([
            'meta' => [
                'message' => 'Genre successfully deleted!',
            ],
        ]);
    }
}
