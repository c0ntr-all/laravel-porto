<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;

class GetUserProfileAction extends BaseAction
{
    public function handle(): ?Authenticatable
    {
        $user = auth()->user();

        if ($user instanceof User) {
            try {
                $user->load('roles');
            } catch (\Throwable) {
                // Roles table may be absent; profile still returns the user.
            }
        }

        return $user;
    }

    public function asController(): JsonResponse
    {
        $user = $this->handle();

        if (!$user) {
            abort(401);
        }

        return fractal($user, new UserTransformer())
            ->withResourceName(ContainerAliasEnum::USER->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
