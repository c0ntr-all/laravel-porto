<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PostUpdateDto extends Data
{
    public int $user_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    public string $date;
    public string $time;

    public function __construct(
    ) {
    }
}
