<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Transformers;

use App\Containers\AppSection\Attachment\UI\API\Transformers\AttachmentTransformer;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
use App\Containers\TaskManagerSection\Task\UI\API\Transformers\TaskTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Album in album page
 */
class PostTransformer extends TransformerAbstract
{
    public function __construct(
        private readonly int $userId
    )
    {
    }
    protected array $availableIncludes = [
        'user',
        'tags',
        'attachments',
        'customFields',
        'movies',
        'movie',
        'tasks',
        'task',
        'albums',
        'album',
    ];

    public function transform(Post $post): array
    {
        return [
            'id' => (string) $post->id,
            'uuid' => (string) $post->uuid,
            'title' => $post->title,
            'content' => $post->content,
            'content_type' => $post->content_type->value,
            'is_draft' => $post->is_draft,
            'date' => $post->date->format('Y-m-d'),
            'time' => $post->time?->format('H:i'),
            'watch' => $post->watchProgress()?->toArray(),
            'started_at' => $post->startedAt(),
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeUser(Post $post): Item
    {
        return $this->item($post->user, new UserTransformer(), 'user');
    }

    public function includeTags(Post $post): Collection
    {
        return $this->collection($post->tagsForUser($this->userId)->get(), new TagTransformer(), 'tags');
    }

    public function includeAttachments(Post $post): Collection
    {
        $attachments = $post->relationLoaded('attachments')
            ? $post->attachments
            : $post->attachments()->with('fileable')->get();

        return $this->collection($attachments, new AttachmentTransformer(), 'attachments');
    }

    public function includeCustomFields(Post $post): Collection
    {
        $customFields = $post->relationLoaded('customFields')
            ? $post->customFields
            : $post->customFields()->get();

        return $this->collection($customFields, new CustomFieldTransformer(), 'custom_fields')
            ->setMeta(['count' => $customFields->count()]);
    }

    public function includeMovies(Post $post): Collection
    {
        $movies = $post->relationLoaded('movies')
            ? $post->movies
            : $post->movies()->with(['genres', 'countries'])->get();

        return $this->collection($movies, new MovieTransformer(), ContainerAliasEnum::MOVIE->value);
    }

    public function includeMovie(Post $post): Item|NullResource
    {
        $movie = $post->relationLoaded('movies')
            ? $post->movies->first()
            : $post->movies()->with(['genres', 'countries'])->first();

        if (!$movie) {
            return $this->null();
        }

        return $this->item($movie, new MovieTransformer(), ContainerAliasEnum::MOVIE->value);
    }

    public function includeTasks(Post $post): Collection
    {
        $tasks = $post->relationLoaded('tasks')
            ? $post->tasks
            : $post->tasks()->get();

        return $this->collection($tasks, new TaskTransformer(), ContainerAliasEnum::TM_TASK->value);
    }

    public function includeTask(Post $post): Item|NullResource
    {
        $task = $post->relationLoaded('tasks')
            ? $post->tasks->first()
            : $post->tasks()->first();

        if (!$task) {
            return $this->null();
        }

        return $this->item($task, new TaskTransformer(), ContainerAliasEnum::TM_TASK->value);
    }

    public function includeAlbums(Post $post): Collection
    {
        $albums = $post->relationLoaded('albums')
            ? $post->albums
            : $post->albums()->get();

        return $this->collection($albums, new AlbumTransformer(), ContainerAliasEnum::GALLERY_ALBUM->value);
    }

    public function includeAlbum(Post $post): Item|NullResource
    {
        $album = $post->relationLoaded('albums')
            ? $post->albums->first()
            : $post->albums()->first();

        if (!$album) {
            return $this->null();
        }

        return $this->item($album, new AlbumTransformer(), ContainerAliasEnum::GALLERY_ALBUM->value);
    }
}
