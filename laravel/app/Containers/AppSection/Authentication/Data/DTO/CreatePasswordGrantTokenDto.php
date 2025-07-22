<?php declare(strict_types=1);

namespace App\Containers\AppSection\Authentication\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreatePasswordGrantTokenDto extends Data
{
    public string $email;
    public string $password;

    public function __construct(
    ) {
    }
}
