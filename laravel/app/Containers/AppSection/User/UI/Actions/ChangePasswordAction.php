<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\Actions;

use App\Containers\AppSection\User\Data\DTO\ChangePasswordDto;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\ChangePasswordTask;
use App\Containers\AppSection\User\UI\API\Requests\ChangePasswordRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ChangePasswordAction extends BaseAction
{
    public function __construct(
        private readonly ChangePasswordTask $changePasswordTask
    ) {
    }

    public function handle(User $user, ChangePasswordDto $dto): User
    {
        return $this->changePasswordTask->run($user, $dto);
    }

    public function asController(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();
        $dto = ChangePasswordDto::from([
            'current_password' => $validated['current_password'],
            'password' => $validated['password'],
        ]);
        $this->handle($user, $dto);

        return new JsonResponse([
            'meta' => [
                'message' => 'Пароль успешно изменён',
            ],
        ]);
    }
}
