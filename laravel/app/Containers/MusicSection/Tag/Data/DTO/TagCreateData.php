<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use Spatie\LaravelData\Data;

class TagCreateData extends Data
{
    public int $user_id;
    public ?int $parent_id = null;
    public string $name;
    public ?string $content = null;
    public bool $is_base = true;

    public function __construct(
    ) {
    }
}
