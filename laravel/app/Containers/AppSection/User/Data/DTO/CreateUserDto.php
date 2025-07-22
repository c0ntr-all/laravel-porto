<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateUserDto extends Data
{
    public string $name;
    public string $email;
    public string $password;

    public function __construct()
    {
    }
}
