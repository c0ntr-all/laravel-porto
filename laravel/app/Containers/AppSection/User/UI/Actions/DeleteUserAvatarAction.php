<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\DeleteUserAvatarTask;
use App\Containers\AppSection\User\UI\API\Requests\DeleteUserAvatarRequest;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteUserAvatarAction extends BaseAction
{
    public function __construct(
        private readonly DeleteUserAvatarTask $deleteUserAvatarTask
    ) {
    }

    public function handle(User $user): User
    {
        return $this->deleteUserAvatarTask->run($user);
    }

    public function asController(DeleteUserAvatarRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user = $this->handle($user);

        try {
            $user->load('roles');
        } catch (\Throwable) {
            // Roles table may be absent; profile still returns the user.
        }

        return fractal($user, new UserTransformer())
            ->withResourceName(ContainerAliasEnum::USER->value)
            ->addMeta(['message' => 'Аватар удалён'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
