<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\DTO;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Ship\Parents\DTO\Data;

class MovieCreateData extends Data
{
    public int $kp_id;
    public string $title;
    public int $year;
    public MovieTypeEnum $type;
    public ?string $cover = null;
    public ?float $kp_rating = null;
    public ?string $kp_img = null;

    public function __construct()
    {
    }
}
