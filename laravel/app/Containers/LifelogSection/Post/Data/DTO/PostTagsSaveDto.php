<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PostTagsSaveDto extends Data
{
    public int $user_id;
    public array|null $tags = null;
    public array|null $new_tags = null;

    public function __construct(
    ) {
    }
}
