<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\Repositories;

use App\Containers\AppSection\User\Data\DTO\CreateUserDto;
use App\Containers\AppSection\User\Data\DTO\UpdateUserProfileDto;
use App\Containers\AppSection\User\Models\User;
use Spatie\LaravelData\Optional;

class UserRepository
{
    public function createUser(CreateUserDto $dto): User
    {
        return User::create($dto->toArray());
    }

    public function updateProfile(User $user, UpdateUserProfileDto $dto): User
    {
        $attributes = [];

        foreach (['name', 'email'] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $user->update($attributes);
        }

        return $user->refresh();
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->update([
            'password' => $password,
        ]);

        return $user->refresh();
    }

    public function updateAvatar(User $user, ?string $avatar): User
    {
        $user->update([
            'avatar' => $avatar,
        ]);

        return $user->refresh();
    }
}
