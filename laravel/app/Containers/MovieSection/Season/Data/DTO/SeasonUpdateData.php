<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class SeasonUpdateData extends Data
{
    public int|Optional $movie_id;
    public int|Optional|null $kp_id;
    public int|Optional|null $kp_season_id;
    public int|Optional|null $kp_movie_id;
    public string|Optional|null $name;
    public string|Optional|null $en_name;
    public int|Optional $number;
    public string|Optional|null $air_date;
    public int|Optional|null $episodes_count;
    public int|Optional|null $duration;
    public string|Optional|null $poster;
    public string|Optional|null $poster_preview;

    public function __construct()
    {
    }
}
