<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ProfessionCreateData extends Data
{
    public string $en_name;
    public ?string $name = null;

    public function __construct()
    {
    }
}
