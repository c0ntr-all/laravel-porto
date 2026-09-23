<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Season\Data\DTO\SeasonCreateData;
use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Tasks\CreateSeasonTask;
use App\Containers\MovieSection\Season\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Season\UI\API\Transformers\SeasonTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateSeasonAction extends BaseAction
{
    public function __construct(
        private readonly CreateSeasonTask $createSeasonTask,
    ) {
    }

    public function handle(SeasonCreateData $dto): Season
    {
        return $this->createSeasonTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $season = $this->handle(SeasonCreateData::from($request->validated()));

        return fractal($season, new SeasonTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_SEASON->value)
            ->addMeta(['message' => 'Season created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
