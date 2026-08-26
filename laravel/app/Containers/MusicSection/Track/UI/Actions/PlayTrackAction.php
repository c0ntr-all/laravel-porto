<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\ResolveTrackAudioPathTask;
use App\Containers\MusicSection\Track\Tasks\StreamTrackAudioTask;
use App\Containers\MusicSection\Track\UI\API\Requests\PlayRequest;
use App\Ship\Parents\Actions\BaseAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlayTrackAction extends BaseAction
{
    public function __construct(
        private readonly ResolveTrackAudioPathTask $resolveTrackAudioPathTask,
        private readonly StreamTrackAudioTask $streamTrackAudioTask,
    ) {
    }

    public function handle(Track $track): BinaryFileResponse
    {
        $absolutePath = $this->resolveTrackAudioPathTask->run($track);
        $filename = basename(str_replace('\\', '/', $track->path ?? $absolutePath));

        return $this->streamTrackAudioTask->run($absolutePath, $filename);
    }

    public function asController(Track $track, PlayRequest $request): BinaryFileResponse
    {
        return $this->handle($track);
    }
}
