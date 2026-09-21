<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class ProfessionUpdateData extends Data
{
    public string|Optional $en_name;
    public string|Optional|null $name;

    public function __construct()
    {
    }
}
