<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CommentUpdateData extends Data
{
    public string $content;

    public function __construct(
    ) {
    }
}
