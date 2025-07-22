<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\Repositories;

use App\Containers\AppSection\User\Data\DTO\CreateUserDto;
use App\Containers\AppSection\User\Models\User;

class UserRepository
{
    public function createUser(CreateUserDto $dto): User
    {
        return User::create($dto->toArray());
    }
}
