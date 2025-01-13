<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CommentCreateData extends Data
{
    public int $user_id;
    public int $commentable_id;
    public string $commentable_type;
    public string $content;

    public function __construct(
    ) {
    }
}
