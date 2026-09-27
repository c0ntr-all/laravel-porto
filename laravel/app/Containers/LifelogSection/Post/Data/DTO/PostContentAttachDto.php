<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Containers\LifelogSection\Post\Data\ValueObjects\SeriesWatchProgress;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\DTO\Data;

class PostContentAttachDto extends Data
{
    public PostContentTypeEnum $content_type;
    public ?int $movie_id = null;
    public ?string $movie_title = null;
    public ?array $watch = null;
    public ?string $started_at = null;
    public bool $has_started_at_input = false;

    public function __construct()
    {
    }

    public function hasMoviePayload(): bool
    {
        return $this->movie_id !== null
            || ($this->movie_title !== null && trim($this->movie_title) !== '');
    }

    public function hasWatchPayload(): bool
    {
        return $this->watch !== null && $this->watch !== [];
    }

    public function hasStartedAtInput(): bool
    {
        return $this->has_started_at_input;
    }

    public function watchProgress(): ?SeriesWatchProgress
    {
        if (!$this->hasWatchPayload()) {
            return null;
        }

        return SeriesWatchProgress::fromArray($this->watch);
    }

    public function startedAt(): ?string
    {
        if ($this->started_at === null || trim($this->started_at) === '') {
            return null;
        }

        return trim($this->started_at);
    }
}
