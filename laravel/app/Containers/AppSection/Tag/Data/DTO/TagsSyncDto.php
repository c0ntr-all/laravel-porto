<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagsSyncDto extends Data
{
    public array $tags = [];

    public function __construct(
    ) {
    }
}
