<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PersonCreateData extends Data
{
    public int $kp_id;
    public int $profession_id;
    public string $name;
    public ?string $en_name = null;
    public ?string $photo = null;

    public function __construct()
    {
    }
}
