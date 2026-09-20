<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\DTO\Data;

class PostContentAttachDto extends Data
{
    public PostContentTypeEnum $content_type;
    public ?int $movie_id = null;
    public ?string $movie_title = null;

    public function __construct()
    {
    }

    public function hasMoviePayload(): bool
    {
        return $this->movie_id !== null
            || ($this->movie_title !== null && trim($this->movie_title) !== '');
    }
}
