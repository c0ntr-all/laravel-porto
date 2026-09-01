<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Transformers;

use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Helpers\DateHelper;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class VideoTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'album',
        'comments',
    ];

    public function transform(Video $video): array
    {
        return [
            'id' => (string) $video->id,
            'album_id' => (string) $video->album_id,
            'source' => $video->source,
            'width' => $video->width,
            'height' => $video->height,
            'duration' => DateHelper::secondsToDatetime($video->duration),
            'original_name' => $video->original_name,
            'original_path' => $video->base_path,
            'list_thumb_path' => $video->list_thumb_path,
            'description' => $video->description,
            'saved_from_id' => $video->saved_from_id ? (string) $video->saved_from_id : null,
            'created_at' => $video->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $video->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeAlbum(Video $video): ?Item
    {
        $album = $video->relationLoaded('album') ? $video->album : $video->album()->first();

        if ($album === null) {
            return null;
        }

        return $this->item($album, new AlbumTransformer(), 'albums');
    }

    public function includeComments(Video $video): Collection
    {
        $comments = $video->relationLoaded('comments') ? $video->comments : $video->comments()->get();

        return $this->collection($comments, new CommentTransformer(), 'comments')
                    ->setMeta(['count' => $comments->count()]);
    }
}
