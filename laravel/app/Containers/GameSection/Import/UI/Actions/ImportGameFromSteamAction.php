<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\UI\Actions;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Containers\GameSection\Import\Exceptions\SteamStoreException;
use App\Containers\GameSection\Import\Support\SteamStoreUrl;
use App\Containers\GameSection\Import\Tasks\FetchSteamStorePageTask;
use App\Containers\GameSection\Import\Tasks\ParseSteamStorePageTask;
use App\Containers\GameSection\Import\Tasks\UpsertImportedGameTask;
use App\Containers\GameSection\Import\UI\API\Requests\ImportRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ImportGameFromSteamAction extends BaseAction
{
    public function __construct(
        private readonly FetchSteamStorePageTask $fetchSteamStorePageTask,
        private readonly ParseSteamStorePageTask $parseSteamStorePageTask,
        private readonly UpsertImportedGameTask $upsertImportedGameTask,
    ) {}

    public function handle(int $steamId): Game
    {
        $page = $this->fetchSteamStorePageTask->run($steamId);
        $parsed = $this->parseSteamStorePageTask->run($page);

        return $this->upsertImportedGameTask->run($parsed);
    }

    public function asController(ImportRequest $request): JsonResponse
    {
        try {
            $game = $this->handle((int) $request->validated('steam_id'));
        } catch (SteamStoreException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->httpStatus);
        }

        $created = $game->wasRecentlyCreated;

        return fractal($game, new GameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME->value)
            ->addMeta([
                'message' => $created ? 'Game imported successfully!' : 'Game updated from Steam.',
                'was_created' => $created,
                'source_url' => SteamStoreUrl::app((int) $game->steam_id),
            ])
            ->respond($created ? 201 : 200, [], JSON_PRETTY_PRINT);
    }
}
