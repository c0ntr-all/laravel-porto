<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Season\Data\DTO\SeasonUpdateData;
use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Tasks\UpdateSeasonTask;
use App\Containers\MovieSection\Season\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateSeasonAction extends BaseAction
{
    public function __construct(
        private readonly UpdateSeasonTask $updateSeasonTask,
    ) {
    }

    public function handle(Season $season, SeasonUpdateData $dto): Season
    {
        return $this->updateSeasonTask->run($season, $dto);
    }

    public function asController(Season $season, UpdateRequest $request): JsonResponse
    {
        $season = $this->handle($season, SeasonUpdateData::from($request->validated()));

        return fractal($season, new SeasonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_SEASON->value)
            ->addMeta(['message' => 'Season updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
