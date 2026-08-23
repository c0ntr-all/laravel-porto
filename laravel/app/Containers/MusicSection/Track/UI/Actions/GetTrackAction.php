<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetTrackAction extends BaseAction
{
    public function handle(Track $track): Track
    {
        return $track->load(['tags', 'artists', 'rate', 'album']);
    }

    public function asController(Track $track, GetRequest $request): JsonResponse
    {
        $track = $this->handle($track);

        return fractal($track, new TrackTransformer())
            ->withResourceName('tracks')
            ->parseIncludes(['tags', 'artists'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
