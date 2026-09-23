<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Data\DTO\EpisodeUpdateData;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Tasks\UpdateEpisodeTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateEpisodeAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEpisodeTask $updateEpisodeTask,
    ) {
    }

    public function handle(Episode $episode, EpisodeUpdateData $dto): Episode
    {
        return $this->updateEpisodeTask->run($episode, $dto);
    }

    public function asController(Episode $episode, UpdateRequest $request): JsonResponse
    {
        $episode = $this->handle($episode, EpisodeUpdateData::from($request->validated()));

        return fractal($episode, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->addMeta(['message' => 'Episode updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
