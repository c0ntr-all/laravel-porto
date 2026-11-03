<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\UI\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\OwnedGame\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\OwnedGame\Tasks\UpsertOwnedGamesFromSteamTask;
use App\Containers\GameSection\OwnedGame\UI\API\Requests\ImportRequest;
use App\Containers\GameSection\OwnedGame\UI\API\Transformers\OwnedGameTransformer;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ImportOwnedGamesFromSteamAction extends BaseAction
{
    public function __construct(
        private readonly UpsertOwnedGamesFromSteamTask $upsertOwnedGamesFromSteamTask,
    ) {}

    /**
     * @return array{
     *     owned_games: \Illuminate\Database\Eloquent\Collection<int, \App\Containers\GameSection\OwnedGame\Models\OwnedGame>,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     game_count: int
     * }
     */
    public function handle(User $user): array
    {
        return $this->upsertOwnedGamesFromSteamTask->run($user);
    }

    public function asController(ImportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $result = $this->handle($user);
        } catch (SteamUserNotLinkedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (SteamWebApiException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->httpStatus);
        }

        return fractal($result['owned_games'], new OwnedGameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_OWNED_GAME->value)
            ->addMeta([
                'message' => 'Owned games imported from Steam.',
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'total' => $result['total'],
                'game_count' => $result['game_count'],
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
