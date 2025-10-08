<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PostTagsSaveDto extends Data
{
    public int $user_id;
    public array|null $tags = [];
    public array|null $new_tags = [];

    public function __construct(
    ) {
    }
}
