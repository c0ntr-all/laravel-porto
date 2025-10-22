<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PostTagsSyncDto extends Data
{
    public int $user_id;
    public array|null $tags = null;
    public array|null $new_tags = null;

    public function __construct(
    ) {
    }
}
