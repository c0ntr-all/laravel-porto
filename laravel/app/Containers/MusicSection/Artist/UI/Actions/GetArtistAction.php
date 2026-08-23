<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class GetArtistAction extends BaseAction
{

    public function handle(Artist $artist): Artist
    {
        return $artist->load(['albums', 'tags']);
    }

    public function asController(Artist $artist, GetRequest $request): JsonResponse
    {
        $artist = $this->handle($artist);

        return fractal($artist, new ArtistTransformer())
            ->withResourceName('artists')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
