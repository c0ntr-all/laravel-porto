<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagUpdateData extends Data
{
    public ?int $parent_id = null;
    public ?int $group_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?bool $is_active = null;

    public function __construct()
    {
    }
}
