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
    public bool|Optional $is_draft;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public Carbon|Optional $date;
    #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
    public Carbon|Optional|null $time;
    public array|Optional|null $tags;
    public array|Optional|null $new_tags;
    public array|Optional|null $deleted_attachments_ids;
    public array|Optional|null $attachments;
    public int|Optional|null $movie_id;
    public string|Optional|null $movie_title;
    public array|Optional|null $watch;
    public string|Optional|null $started_at;
    public int|Optional|null $task_id;
    public int|Optional|null $album_id;
    public int|Optional|null $note_id;

    public function __construct()
    {
    }

    public function toContentAttachDto(?PostContentTypeEnum $fallbackContentType = null): ?PostContentAttachDto
    {
        $movieId = $this->movie_id instanceof Optional ? null : $this->movie_id;
        $movieTitle = $this->movie_title instanceof Optional ? null : $this->movie_title;
        $watch = $this->watch instanceof Optional ? null : $this->watch;
        $hasStartedAtInput = !($this->started_at instanceof Optional);
        $startedAt = $hasStartedAtInput ? $this->started_at : null;
        $taskId = $this->task_id instanceof Optional ? null : $this->task_id;
        $albumId = $this->album_id instanceof Optional ? null : $this->album_id;
        $noteId = $this->note_id instanceof Optional ? null : $this->note_id;

        $hasMovie = $movieId !== null || ($movieTitle !== null && trim((string) $movieTitle) !== '');
        $hasWatch = $watch !== null && $watch !== [];
        $hasTask = $taskId !== null;
        $hasAlbum = $albumId !== null;
        $hasNote = $noteId !== null;
        $hasContentTypeChange = $this->content_type instanceof PostContentTypeEnum;

        if (!$hasMovie && !$hasWatch && !$hasStartedAtInput && !$hasTask && !$hasAlbum && !$hasNote && !$hasContentTypeChange) {
            return null;
        }

        $contentType = $this->content_type instanceof PostContentTypeEnum
            ? $this->content_type
            : ($fallbackContentType ?? PostContentTypeEnum::DEFAULT);

        return PostContentAttachDto::from([
            'content_type' => $contentType,
            'movie_id' => $movieId,
            'movie_title' => $movieTitle,
            'watch' => $watch,
            'started_at' => $startedAt,
            'has_started_at_input' => $hasStartedAtInput,
            'task_id' => $taskId,
            'album_id' => $albumId,
            'note_id' => $noteId,
        ]);
    }
}
