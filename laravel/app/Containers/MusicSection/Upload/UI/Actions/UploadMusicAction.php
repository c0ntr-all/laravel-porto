<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Upload\Tasks\PrepareUploadingMusicTreeTask;
use App\Containers\MusicSection\Upload\Tasks\UploadMusicTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\UploadRequest;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Exception;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMusicAction
{
    use AsAction;

    public function __construct(
        private readonly PrepareUploadingMusicTreeTask $prepareUploadingMusicTreeTask,
        private readonly UploadMusicTask $uploadMusicTask
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(array $data): array
    {
        return $this->prepareUploadingMusicTreeTask->run($data);
    }

    /**
     * @param Artist $artist
     * @param UploadRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function asController(Artist $artist, UploadRequest $request): JsonResponse
    {
        $result = $this->handle($request->validated());

        dd($result);

//        return fractal($updatedArtist, new ArtistTransformer())
//            ->parseIncludes(['tags'])
//            ->withResourceName('artists')
//            ->addMeta(['message' => 'Artist updated successfully!'])
//            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
