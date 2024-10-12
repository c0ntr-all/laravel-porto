<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Data\DTO\RateTrackData;
use App\Containers\MusicSection\Track\Models\Rate;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\RateTrackTask;
use App\Containers\MusicSection\Track\UI\API\Requests\RateRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class RateTrackAction
{
    use AsAction;

    public function __construct(
        private readonly RateTrackTask $rateTrackTask
    )
    {
    }

    public function handle(Track $track, RateTrackData $dto): Rate
    {
        return $this->rateTrackTask->run($track, $dto);
    }

    public function asController(RateRequest $request, Track $track): JsonResponse
    {
        $dto = RateTrackData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $this->handle($track, $dto);

        return response()->json(
            ['meta' => [
                'message' => 'The track was successfully rated'
            ]]
        );
    }
}
