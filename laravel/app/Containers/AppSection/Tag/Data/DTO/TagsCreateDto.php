<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagsCreateDto extends Data
{
    public int $user_id;
    public array $new_tags;

    public function __construct(
    ) {
    }
}
