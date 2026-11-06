<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class PostCreateDto extends Data
{
    public int $user_id;
    public ?string $title = null;
    public ?string $content = null;
    public PostContentTypeEnum $content_type = PostContentTypeEnum::DEFAULT;
    public bool $is_draft = false;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public Carbon $date;
    #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
    public ?Carbon $time;
    public ?array $tags = null;
    public ?array $new_tags = null;
    public ?array $attachments = [];
    public ?int $movie_id = null;
    public ?string $movie_title = null;
    public ?array $watch = null;
    public ?string $started_at = null;
    public ?int $task_id = null;
    public ?int $album_id = null;
    public ?int $note_id = null;

    public function __construct(
    ) {
    }

    public function toContentAttachDto(): PostContentAttachDto
    {
        return PostContentAttachDto::from([
            'content_type' => $this->content_type,
            'movie_id' => $this->movie_id,
            'movie_title' => $this->movie_title,
            'watch' => $this->watch,
            'started_at' => $this->started_at,
            'has_started_at_input' => true,
            'task_id' => $this->task_id,
            'album_id' => $this->album_id,
            'note_id' => $this->note_id,
            'has_content_type_input' => true,
            'has_task_input' => $this->task_id !== null,
            'has_album_input' => $this->album_id !== null,
            'has_note_input' => $this->note_id !== null,
        ]);
    }
}
