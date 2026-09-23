<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Data\DTO;

use App\Ship\Parents\DTO\Data;

class EpisodeCreateData extends Data
{
    public int $season_id;
    public ?int $kp_id = null;
    public ?int $kp_season_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $en_description = null;
    public int $number;
    public ?int $duration = null;
    public ?string $air_date = null;
    public ?string $still = null;
    public ?string $still_preview = null;

    public function __construct()
    {
    }
}
