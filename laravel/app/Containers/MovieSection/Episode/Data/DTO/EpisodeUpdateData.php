<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class EpisodeUpdateData extends Data
{
    public int|Optional $season_id;
    public int|Optional|null $kp_id;
    public int|Optional|null $kp_season_id;
    public string|Optional|null $name;
    public string|Optional|null $description;
    public string|Optional|null $en_description;
    public int|Optional $number;
    public int|Optional|null $duration;
    public string|Optional|null $air_date;
    public string|Optional|null $still;
    public string|Optional|null $still_preview;

    public function __construct()
    {
    }
}
