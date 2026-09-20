<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\DTO;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Ship\Parents\DTO\Data;

class MovieCreateData extends Data
{
    public ?int $kp_id = null;
    public string $title;
    public ?string $description = null;
    public ?string $short_description = null;
    public ?int $year = null;
    public MovieTypeEnum $type = MovieTypeEnum::MOVIE;
    public ?string $cover = null;
    public ?float $kp_rating = null;
    public ?string $kp_img = null;

    public function __construct()
    {
    }
}
