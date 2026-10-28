<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\UI\Actions;

use App\Containers\GameSection\Import\Models\GameImport;
use App\Containers\GameSection\Import\UI\API\Requests\GetRequest;
use App\Containers\GameSection\Import\UI\API\Transformers\GameImportTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetGameImportAction extends BaseAction
{
    public function handle(GameImport $import, int $userId): GameImport
    {
        if ((int) $import->user_id !== $userId) {
            throw new AccessDeniedHttpException;
        }

        return $import->load('game');
    }

    public function asController(GameImport $gameImport, GetRequest $request): JsonResponse
    {
        $import = $this->handle($gameImport, (int) auth()->id());

        return fractal($import, new GameImportTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_IMPORT->value)
            ->parseIncludes(['game'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
