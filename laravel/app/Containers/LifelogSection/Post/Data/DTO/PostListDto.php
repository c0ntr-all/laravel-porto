<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PostListDto extends Data
{
    public int $user_id;

    public function __construct(
    ) {
    }
}
