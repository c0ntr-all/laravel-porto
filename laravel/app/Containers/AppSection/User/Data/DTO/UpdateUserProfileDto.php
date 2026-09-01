<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class UpdateUserProfileDto extends Data
{
    public string|Optional $name;
    public string|Optional $email;

    public function __construct()
    {
    }
}
