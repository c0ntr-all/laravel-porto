<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\Tasks\CreateArtistTask;
use App\Containers\MusicSection\Artist\Tasks\UploadArtistCoverTask;
use App\Containers\MusicSection\Artist\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateArtistAction extends BaseAction
{
    public function __construct(
        private readonly CreateArtistTask $createArtistTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadArtistCoverTask $uploadArtistCoverTask
    )
    {
    }

    public function handle(array $requestData): Artist
    {
        return DB::transaction(function () use ($requestData) {
            $dto = CreateArtistDto::from([
                'user_id' => auth()->id(),
                'name' => $requestData['name'],
                'description' => $requestData['description'] ?? null,
                'country_id' => isset($requestData['country_id']) ? (string) $requestData['country_id'] : null,
                'path' => $requestData['path'] ?? (Str::slug($requestData['name']) ?: 'artist-'.uniqid()),
                'image' => null,
            ]);

            $artist = $this->createArtistTask->run($dto);

            if (!empty($requestData['image_file'])) {
                $artist->image = $this->uploadArtistCoverTask->run(
                    $requestData['image_file'],
                    (string) $artist->id
                );
                $artist->save();
            }

            if (!empty($requestData['tags'])) {
                $this->syncTagsTask->run($artist, SyncTagsDto::from(['tags' => $requestData['tags']]));
            }

            return $artist->load('tags');
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $artist = $this->handle($request->validated());

        return fractal($artist, new ArtistTransformer())
            ->parseIncludes(['tags'])
            ->withResourceName('artists')
            ->addMeta(['message' => 'Artist created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
