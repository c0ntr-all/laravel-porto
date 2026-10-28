<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\UI\Actions;

use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Containers\GameSection\Import\Data\DTO\GameImportCreateData;
use App\Containers\GameSection\Import\Data\DTO\ParsedSteamGameDto;
use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;
use App\Containers\GameSection\Import\Enums\GameImportStatusEnum;
use App\Containers\GameSection\Import\Exceptions\SteamStoreException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreGameNotFoundException;
use App\Containers\GameSection\Import\Models\GameImport;
use App\Containers\GameSection\Import\Support\GameImportLogMeta;
use App\Containers\GameSection\Import\Support\GameStateSnapshot;
use App\Containers\GameSection\Import\Support\SteamStoreUrl;
use App\Containers\GameSection\Import\Tasks\CreateGameImportLogTask;
use App\Containers\GameSection\Import\Tasks\FetchSteamStorePageTask;
use App\Containers\GameSection\Import\Tasks\FinalizeGameImportLogTask;
use App\Containers\GameSection\Import\Tasks\ParseSteamStorePageTask;
use App\Containers\GameSection\Import\Tasks\UpsertImportedGameTask;
use App\Containers\GameSection\Import\UI\API\Requests\ImportRequest;
use App\Containers\GameSection\Import\UI\API\Transformers\GameImportTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportGameFromSteamAction extends BaseAction
{
    public function __construct(
        private readonly CreateGameImportLogTask $createGameImportLogTask,
        private readonly FetchSteamStorePageTask $fetchSteamStorePageTask,
        private readonly ParseSteamStorePageTask $parseSteamStorePageTask,
        private readonly UpsertImportedGameTask $upsertImportedGameTask,
        private readonly FinalizeGameImportLogTask $finalizeGameImportLogTask,
        private readonly GameRepository $gameRepository,
    ) {}

    public function handle(int $steamId, int $userId): GameImport
    {
        $import = $this->createGameImportLogTask->run(GameImportCreateData::from([
            'user_id' => $userId,
            'steam_id' => $steamId,
            'source_url' => SteamStoreUrl::app($steamId),
        ]));
        $startedAtNs = hrtime(true);
        $page = null;
        $parsed = null;

        try {
            $page = $this->fetchSteamStorePageTask->run($steamId);
            $parsed = $this->parseSteamStorePageTask->run($page);

            $result = DB::transaction(function () use ($parsed) {
                $existing = $this->gameRepository->findBySteamId($parsed->steam_id, forUpdate: true);
                $before = $existing !== null ? GameStateSnapshot::fromGame($existing) : null;
                $game = $this->upsertImportedGameTask->run($parsed);
                $after = GameStateSnapshot::fromGame($game);

                return [
                    'game' => $game,
                    'was_created' => $game->wasRecentlyCreated,
                    'before' => $before,
                    'after' => $after,
                    'changes' => GameStateSnapshot::diff($before, $after),
                ];
            });

            return $this->finalizeGameImportLogTask->run(
                import: $import,
                status: GameImportStatusEnum::Completed,
                startedAtNs: $startedAtNs,
                gameId: (int) $result['game']->id,
                wasCreated: $result['was_created'],
                httpStatus: $page->http_status,
                parsedPayload: $parsed->toArray(),
                meta: GameImportLogMeta::from(
                    $page,
                    $parsed,
                    null,
                    201,
                    $result['before'],
                    $result['after'],
                    $result['changes'],
                    $result['was_created'],
                ),
            )->load('game');
        } catch (SteamStoreException $exception) {
            $failed = $this->fail($import, $startedAtNs, $page, $parsed, $exception, $exception->httpStatus);

            throw $exception->withImport($failed);
        } catch (Throwable $exception) {
            $failed = $this->fail($import, $startedAtNs, $page, $parsed, $exception, 500);

            throw new SteamStoreException(
                'Failed to import game from Steam.',
                500,
                $failed,
                $exception,
            );
        }
    }

    public function asController(ImportRequest $request): JsonResponse
    {
        try {
            $import = $this->handle((int) $request->validated('steam_id'), (int) auth()->id());
            $status = 201;
            $message = $import->was_created ? 'Game imported successfully!' : 'Game updated from Steam.';
        } catch (SteamStoreException $exception) {
            $import = $exception->import ?? throw $exception;
            $status = $exception->httpStatus;
            $message = $exception->getMessage();
        }

        return fractal($import, new GameImportTransformer)
            ->parseIncludes(['game'])
            ->withResourceName(ContainerAliasEnum::GAME_IMPORT->value)
            ->addMeta(['message' => $message])
            ->respond($status, [], JSON_PRETTY_PRINT);
    }

    private function fail(
        GameImport $import,
        int $startedAtNs,
        ?SteamStorePageDto $page,
        ?ParsedSteamGameDto $parsed,
        Throwable $exception,
        int $apiStatus,
    ): GameImport {
        return $this->finalizeGameImportLogTask->run(
            import: $import,
            status: GameImportStatusEnum::Failed,
            startedAtNs: $startedAtNs,
            httpStatus: $page?->http_status ?? ($exception instanceof SteamStoreGameNotFoundException ? 404 : null),
            parsedPayload: $parsed?->toArray(),
            errorMessage: $exception->getMessage(),
            meta: GameImportLogMeta::from($page, $parsed, $exception, $apiStatus),
        );
    }
}
