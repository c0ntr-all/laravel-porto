<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagUpdateData extends Data
{
    public ?int $parent_id = null;
    public ?string $name = null;
    public ?string $content = null;
    public ?bool $is_base = null;

    public function __construct()
    {
    }
}
