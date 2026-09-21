<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PersonUpdateData extends Data
{
    public int|Optional $kp_id;
    public int|Optional|null $profession_id;
    public string|Optional $name;
    public string|Optional|null $en_name;
    public string|Optional|null $photo;

    public function __construct()
    {
    }
}
