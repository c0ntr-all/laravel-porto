<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SeasonCreateData extends Data
{
    public int $movie_id;
    public ?int $kp_id = null;
    public ?int $kp_season_id = null;
    public ?int $kp_movie_id = null;
    public ?string $name = null;
    public ?string $en_name = null;
    public int $number;
    public ?string $air_date = null;
    public ?int $episodes_count = null;
    public ?int $duration = null;
    public ?string $poster = null;
    public ?string $poster_preview = null;

    public function __construct()
    {
    }
}
