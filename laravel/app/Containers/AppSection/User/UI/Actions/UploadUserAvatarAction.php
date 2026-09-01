<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\Data\DTO\UploadUserAvatarDto;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\UploadUserAvatarTask;
use App\Containers\AppSection\User\UI\API\Requests\UploadUserAvatarRequest;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UploadUserAvatarAction extends BaseAction
{
    public function __construct(
        private readonly UploadUserAvatarTask $uploadUserAvatarTask
    ) {
    }

    public function handle(User $user, UploadUserAvatarDto $dto): User
    {
        return $this->uploadUserAvatarTask->run($user, $dto);
    }

    public function asController(UploadUserAvatarRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $dto = UploadUserAvatarDto::from($request->validated());
        $user = $this->handle($user, $dto);

        try {
            $user->load('roles');
        } catch (\Throwable) {
            // Roles table may be absent; profile still returns the user.
        }

        return fractal($user, new UserTransformer())
            ->withResourceName(ContainerAliasEnum::USER->value)
            ->addMeta(['message' => 'Аватар обновлён'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
