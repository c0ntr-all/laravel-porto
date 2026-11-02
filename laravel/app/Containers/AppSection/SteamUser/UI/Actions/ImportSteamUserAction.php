<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\UI\Actions;

use App\Containers\AppSection\Steam\Exceptions\SteamWebApiException;
use App\Containers\AppSection\SteamUser\Exceptions\SteamIdAlreadyLinkedException;
use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\SteamUser\Tasks\ImportSteamUserForUserTask;
use App\Containers\AppSection\SteamUser\UI\API\Requests\ImportRequest;
use App\Containers\AppSection\SteamUser\UI\API\Transformers\SteamUserTransformer;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ImportSteamUserAction extends BaseAction
{
    public function __construct(
        private readonly ImportSteamUserForUserTask $importSteamUserForUserTask,
    ) {}

    public function handle(User $user, string $steamId): SteamUser
    {
        return $this->importSteamUserForUserTask->run($user, $steamId);
    }

    public function asController(ImportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $steamUser = $this->handle($user, (string) $request->validated('steam_id'));
        } catch (SteamIdAlreadyLinkedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        } catch (SteamWebApiException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->httpStatus);
        }

        $wasCreated = $steamUser->wasRecentlyCreated;

        return fractal($steamUser, new SteamUserTransformer)
            ->withResourceName(ContainerAliasEnum::STEAM_USER->value)
            ->addMeta([
                'message' => $wasCreated
                    ? 'Steam profile imported successfully!'
                    : 'Steam profile updated successfully!',
                'was_created' => $wasCreated,
            ])
            ->respond($wasCreated ? 201 : 200, [], JSON_PRETTY_PRINT);
    }
}
