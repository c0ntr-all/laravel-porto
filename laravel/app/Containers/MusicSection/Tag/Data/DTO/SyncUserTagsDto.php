<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SyncUserTagsDto extends Data
{
    public int $user_id;

    /** @var array<int, int> */
    public array $tags;

    public function __construct()
    {
    }
}
