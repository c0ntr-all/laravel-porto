<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Transformers;

use App\Containers\AppSection\Attachment\UI\API\Transformers\AttachmentTransformer;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldTransformer;
use App\Containers\AppSection\Tag\UI\API\Transformers\TagTransformer;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;
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
    ];

    public function transform(Post $post): array
    {
        return [
            'id' => (string) $post->id,
            'uuid' => (string) $post->uuid,
            'title' => $post->title,
            'content' => $post->content,
            'content_type' => $post->content_type->value,
            'date' => $post->date->format('Y-m-d'),
            'time' => $post->time?->format('H:i'),
            'watch' => $post->watchProgress()?->toArray(),
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
}
