<?php

namespace App\Containers\AppSection\Authentication\Actions;

use App\Containers\AppSection\Authentication\UI\API\Requests\LogoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Laravel\Passport\Passport;
use Lcobucci\JWT\Configuration;
use Lorisleiva\Actions\Concerns\AsAction;

class LogoutAction
{
    use AsAction;

    public function __construct(
        private readonly Configuration $jwtConfig,
    )
    {
    }

    public function handle()
    {
        // ...
    }

    public function asController(LogoutRequest $request)
    {
        $id = $this->jwtConfig->parser()->parse($request->bearerToken())->claims()->get('jti');
        Passport::token()->where('id', $id)->update(['revoked' => true]);
        Passport::refreshToken()->where('access_token_id', $id)->update(['revoked' => true]);

        Cookie::forget('refreshToken');

        return new JsonResponse([
            'message' => 'Token revoked successfully.',
        ]);
    }
}
