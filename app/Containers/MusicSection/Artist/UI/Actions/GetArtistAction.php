<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetArtistAction
{
    use AsAction;

    public function handle(Artist $artist): Artist
    {
        return $artist->load(['albums', 'tags']);
    }

    public function asController(Artist $artist): JsonResponse
    {
        $artist = $this->handle($artist);

        return fractal($artist, new ArtistTransformer())
            ->withResourceName('artists')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
