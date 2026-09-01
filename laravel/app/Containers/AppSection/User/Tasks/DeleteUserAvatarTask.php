<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Tasks;

use App\Containers\AppSection\User\Data\Repositories\UserRepository;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Facades\Storage;

class DeleteUserAvatarTask extends Task
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function run(User $user): User
    {
        $previous = $user->avatar;
        $user = $this->userRepository->updateAvatar($user, null);

        if (is_string($previous) && $previous !== '' && !str_contains($previous, 'http')) {
            Storage::disk('public')->delete($previous);
        }

        return $user;
    }
}
