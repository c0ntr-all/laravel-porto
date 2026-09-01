<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\Data\DTO\UpdateUserProfileDto;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\UpdateUserProfileTask;
use App\Containers\AppSection\User\UI\API\Requests\UpdateUserProfileRequest;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateUserProfileAction extends BaseAction
{
    public function __construct(
        private readonly UpdateUserProfileTask $updateUserProfileTask
    ) {
    }

    public function handle(User $user, UpdateUserProfileDto $dto): User
    {
        return $this->updateUserProfileTask->run($user, $dto);
    }

    public function asController(UpdateUserProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $dto = UpdateUserProfileDto::from($request->validated());
        $user = $this->handle($user, $dto);

        try {
            $user->load('roles');
        } catch (\Throwable) {
            // Roles table may be absent; profile still returns the user.
        }

        return fractal($user, new UserTransformer())
            ->withResourceName(ContainerAliasEnum::USER->value)
            ->addMeta(['message' => 'Профиль успешно обновлён'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
