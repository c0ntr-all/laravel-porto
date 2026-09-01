<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Tasks;

use App\Containers\AppSection\User\Data\DTO\ChangePasswordDto;
use App\Containers\AppSection\User\Data\Repositories\UserRepository;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class ChangePasswordTask extends Task
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function run(User $user, ChangePasswordDto $dto): User
    {
        if (!Hash::check($dto->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль указан неверно.'],
            ]);
        }

        return $this->userRepository->updatePassword($user, $dto->password);
    }
}
