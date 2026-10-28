<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\UI\Actions;

use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\SteamUser\UI\API\Requests\GetRequest;
use App\Containers\AppSection\SteamUser\UI\API\Transformers\SteamUserTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetSteamUserAction extends BaseAction
{
    public function handle(GetRequest $request): SteamUser
    {
        $steamUser = $request->user()?->steamUser;
        if (! $steamUser instanceof SteamUser) {
            abort(404, 'Steam profile is not linked.');
        }

        return $steamUser;
    }

    public function asController(GetRequest $request): JsonResponse
    {
        $steamUser = $this->handle($request);

        return fractal($steamUser, new SteamUserTransformer)
            ->withResourceName(ContainerAliasEnum::STEAM_USER->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
