<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CommentListDto extends Data
{
    public int $user_id;

    public function __construct(
    ) {
    }
}
