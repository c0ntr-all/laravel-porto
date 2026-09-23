<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class PostUpdateContextDto extends Data
{
    public int $user_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    public PostContentTypeEnum|Optional $content_type;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public Carbon $date;
    #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
    public Carbon $time;
    public array|null $tags;
    public array|null $new_tags;
    public array $deleted_attachments_ids = [];
    public ?array $attachments = [];
    public int|Optional|null $movie_id;
    public string|Optional|null $movie_title;
    public array|Optional|null $watch;

    public function __construct()
    {
    }

    public function toContentAttachDto(?PostContentTypeEnum $fallbackContentType = null): ?PostContentAttachDto
    {
        $movieId = $this->movie_id instanceof Optional ? null : $this->movie_id;
        $movieTitle = $this->movie_title instanceof Optional ? null : $this->movie_title;
        $watch = $this->watch instanceof Optional ? null : $this->watch;

        $hasMovie = $movieId !== null || ($movieTitle !== null && trim((string) $movieTitle) !== '');
        $hasWatch = $watch !== null && $watch !== [];

        if (!$hasMovie && !$hasWatch) {
            return null;
        }

        $contentType = $this->content_type instanceof PostContentTypeEnum
            ? $this->content_type
            : ($fallbackContentType ?? PostContentTypeEnum::MOVIE);

        return PostContentAttachDto::from([
            'content_type' => $contentType,
            'movie_id' => $movieId,
            'movie_title' => $movieTitle,
            'watch' => $watch,
        ]);
    }
}
