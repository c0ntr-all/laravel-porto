<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SyncTagsDto extends Data
{
    public array $tags;

    public function __construct(
    ) {
    }
}
