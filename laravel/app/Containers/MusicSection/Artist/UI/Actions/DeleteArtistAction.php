<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\Tasks\DeleteArtistTask;
use App\Containers\MusicSection\Artist\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteArtistAction extends BaseAction
{
    public function __construct(
        private readonly DeleteArtistTask $deleteArtistTask
    )
    {
    }

    public function handle(Artist $artist): ?bool
    {
        return $this->deleteArtistTask->run($artist);
    }

    public function asController(Artist $artist, DeleteRequest $request): JsonResponse
    {
        $this->handle($artist);

        return response()->json([
            'meta' => [
                'message' => 'Artist successfully deleted!',
            ],
        ]);
    }
}
