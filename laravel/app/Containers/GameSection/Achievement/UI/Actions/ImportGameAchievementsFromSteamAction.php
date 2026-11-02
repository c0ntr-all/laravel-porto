<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Achievement\Tasks\UpsertGameAchievementsFromSteamTask;
use App\Containers\GameSection\Achievement\UI\API\Requests\ImportRequest;
use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ImportGameAchievementsFromSteamAction extends BaseAction
{
    public function __construct(
        private readonly UpsertGameAchievementsFromSteamTask $upsertGameAchievementsFromSteamTask,
    ) {}

    /**
     * @return array{
     *     achievements: \Illuminate\Database\Eloquent\Collection<int, \App\Containers\GameSection\Achievement\Models\Achievement>,
     *     created: int,
     *     updated: int,
     *     total: int
     * }
     */
    public function handle(Game $game, ?string $language = null): array
    {
        return $this->upsertGameAchievementsFromSteamTask->run($game, $language);
    }

    public function asController(Game $game, ImportRequest $request): JsonResponse
    {
        $language = $request->validated('language');

        try {
            $result = $this->handle($game, is_string($language) ? $language : null);
        } catch (GameMissingSteamIdException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (SteamWebApiException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->httpStatus);
        }

        return fractal($result['achievements'], new AchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_ACHIEVEMENT->value)
            ->addMeta([
                'message' => 'Game achievements imported from Steam.',
                'created' => $result['created'],
                'updated' => $result['updated'],
                'total' => $result['total'],
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
