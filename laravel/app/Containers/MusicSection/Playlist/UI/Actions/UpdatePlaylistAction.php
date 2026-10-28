<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistUpdateData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\Tasks\UpdatePlaylistTask;
use App\Containers\MusicSection\Playlist\Tasks\UploadPlaylistCoverTask;
use App\Containers\MusicSection\Playlist\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdatePlaylistAction extends BaseAction
{
    public function __construct(
        private readonly UpdatePlaylistTask $updatePlaylistTask,
        private readonly UploadPlaylistCoverTask $uploadPlaylistCoverTask,
    )
    {
    }

    public function handle(Playlist $playlist, array $requestData): Playlist
    {
        return DB::transaction(function () use ($playlist, $requestData) {
            $payload = [];

            if (array_key_exists('name', $requestData)) {
                $payload['name'] = $requestData['name'];
            }

            if (array_key_exists('description', $requestData)) {
                $description = $requestData['description'];
                $payload['description'] = $description === null || $description === ''
                    ? null
                    : (string) $description;
            }

            if (!empty($requestData['image_file'])) {
                $payload['image'] = $this->uploadPlaylistCoverTask->run(
                    $requestData['image_file'],
                    $playlist->id,
                );
            }

            $dto = PlaylistUpdateData::from($payload);
            $updated = $this->updatePlaylistTask->run($playlist, $dto);

            // Description clear: DTO null is filtered out by repository — set explicitly.
            if (array_key_exists('description', $payload) && $payload['description'] === null) {
                $updated->forceFill(['description' => null])->save();
            }

            return $updated->loadCount('tracks');
        });
    }

    public function asController(Playlist $playlist, UpdateRequest $request): JsonResponse
    {
        $playlist = $this->handle($playlist, $request->validated());

        return fractal($playlist, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->addMeta(['message' => 'Playlist updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
