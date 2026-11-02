<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\UI\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\PlayerAchievement\Tasks\UpsertPlayerAchievementsFromSteamTask;
use App\Containers\GameSection\PlayerAchievement\UI\API\Requests\ImportRequest;
use App\Containers\GameSection\PlayerAchievement\UI\API\Transformers\PlayerAchievementTransformer;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ImportPlayerAchievementsFromSteamAction extends BaseAction
{
    public function __construct(
        private readonly UpsertPlayerAchievementsFromSteamTask $upsertPlayerAchievementsFromSteamTask,
    ) {}

    /**
     * @return array{
     *     player_achievements: \Illuminate\Database\Eloquent\Collection<int, \App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement>,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     catalog_imported: bool,
     *     game_name: string|null
     * }
     */
    public function handle(User $user, Game $game, ?string $language = null): array
    {
        return $this->upsertPlayerAchievementsFromSteamTask->run($user, $game, $language);
    }

    public function asController(Game $game, ImportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $language = $request->validated('language');

        try {
            $result = $this->handle($user, $game, is_string($language) ? $language : null);
        } catch (GameMissingSteamIdException|SteamUserNotLinkedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (SteamWebApiException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->httpStatus);
        }

        return fractal($result['player_achievements'], new PlayerAchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_PLAYER_ACHIEVEMENT->value)
            ->addMeta([
                'message' => 'Player achievements imported from Steam.',
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'total' => $result['total'],
                'catalog_imported' => $result['catalog_imported'],
                'game_name' => $result['game_name'],
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
