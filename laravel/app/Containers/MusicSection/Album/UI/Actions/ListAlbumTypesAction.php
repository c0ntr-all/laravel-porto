<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\UI\API\Requests\ListAlbumTypesRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTypeTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListAlbumTypesAction extends BaseAction
{
    public function __construct(
        private readonly AlbumRepository $albumRepository
    ) {
    }

    public function handle(): Collection
    {
        return $this->albumRepository->listTypes();
    }

    public function asController(ListAlbumTypesRequest $request): JsonResponse
    {
        return fractal($this->handle(), new AlbumTypeTransformer())
            ->withResourceName('album-types')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
