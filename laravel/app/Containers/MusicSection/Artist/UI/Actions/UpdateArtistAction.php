<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\Tasks\UpdateArtistTask;
use App\Containers\MusicSection\Artist\Tasks\UploadArtistCoverTask;
use App\Containers\MusicSection\Artist\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateArtistAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateArtistTask $updateArtistTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadArtistCoverTask $uploadArtistCoverTask
    )
    {
    }

    /**
     * @param Artist $artist
     * @param array $requestData
     * @return Artist
     */
    public function handle(Artist $artist, array $requestData): Artist
    {
        return DB::transaction(function() use ($artist, $requestData) {
            $updateArtistDto = UpdateArtistDto::from($requestData);
            $updateArtistDto->user_id = auth()->user()->id;

            if (!empty($requestData['image_file'])) {
                $updateArtistDto->image = $this->uploadArtistCoverTask->run(
                    $requestData['image_file'],
                    $updateArtistDto->name,
                    $updateArtistDto->name
                );
            }

            $updatedArtist = $this->updateArtistTask->run($artist, $updateArtistDto);

            if (!empty($requestData['tags'])) {
                $syncTagsDto = SyncTagsDto::from($requestData);
                $this->syncTagsTask->run($updatedArtist, $syncTagsDto);
            }

            return $updatedArtist;
        });
    }

    /**
     * @param Artist $artist
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function asController(Artist $artist, UpdateRequest $request): JsonResponse
    {
        $updatedArtist = $this->handle($artist, $request->validated());

        return fractal($updatedArtist, new ArtistTransformer())
            ->parseIncludes(['tags'])
            ->withResourceName('artists')
            ->addMeta(['message' => 'Artist updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
