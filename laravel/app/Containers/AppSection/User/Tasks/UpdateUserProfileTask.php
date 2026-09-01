<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Tasks;

use App\Containers\AppSection\User\Data\DTO\UpdateUserProfileDto;
use App\Containers\AppSection\User\Data\Repositories\UserRepository;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task;

class UpdateUserProfileTask extends Task
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function run(User $user, UpdateUserProfileDto $dto): User
    {
        return $this->userRepository->updateProfile($user, $dto);
    }
}
