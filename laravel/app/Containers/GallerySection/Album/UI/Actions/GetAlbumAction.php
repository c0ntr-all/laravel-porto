<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\Tasks\GetAlbumTask;
use App\Containers\GallerySection\Album\UI\API\Requests\GetAlbumRequest;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetAlbumAction extends BaseAction
{
    public function __construct(
        private readonly GetAlbumTask $getAlbumTask
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function handle(Album $album, array $with = []): Album
    {
        return $this->getAlbumTask->run($album, $with);
    }

    public function asController(Album $album, GetAlbumRequest $request): JsonResponse
    {
        $with = ['images', 'videos', 'user'];

        if ($request->filled('include')) {
            $with = $this->relationsFromInclude((string) $request->query('include'));
        }

        $album = $this->handle($album, $with);

        $fractal = fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->addMeta([
                'count' => (int) ($album->images_count ?? 0),
                'images_count' => (int) ($album->images_count ?? 0),
                'videos_count' => (int) ($album->videos_count ?? 0),
            ]);

        if (!$request->filled('include')) {
            $fractal->parseIncludes(['images', 'videos', 'user']);
        }

        return $fractal->respond(200, [], JSON_PRETTY_PRINT);
    }

    /**
     * @return list<string>
     */
    private function relationsFromInclude(string $include): array
    {
        $with = [];

        if (str_contains($include, 'images')) {
            $with[] = 'images';
        }
        if (str_contains($include, 'videos')) {
            $with[] = 'videos';
        }
        if (str_contains($include, 'user')) {
            $with[] = 'user';
        }

        return $with;
    }
}
