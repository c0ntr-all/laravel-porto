<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Data\DTO\EpisodeCreateData;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Tasks\CreateEpisodeTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Episode\UI\API\Transformers\EpisodeTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateEpisodeAction extends BaseAction
{
    public function __construct(
        private readonly CreateEpisodeTask $createEpisodeTask,
    ) {
    }

    public function handle(EpisodeCreateData $dto): Episode
    {
        return $this->createEpisodeTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $episode = $this->handle(EpisodeCreateData::from($request->validated()));

        return fractal($episode, new EpisodeTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_EPISODE->value)
            ->addMeta(['message' => 'Episode created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
