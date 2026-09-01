<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ChangePasswordDto extends Data
{
    public string $current_password;
    public string $password;

    public function __construct()
    {
    }
}
