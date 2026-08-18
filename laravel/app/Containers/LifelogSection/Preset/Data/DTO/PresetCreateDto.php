<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PresetCreateDto extends Data
{
    public int $user_id;
    public string $title;
    public string $color;
    public string $start_post_id;
    public string $end_post_id;

    public function __construct()
    {
    }
}
