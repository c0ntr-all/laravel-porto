<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagGroupUpdateData extends Data
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $description = null;
    public ?bool $is_system = null;
    public ?bool $is_active = null;
    public ?int $display_order = null;

    public function __construct()
    {
    }
}
