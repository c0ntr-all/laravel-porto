<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Transformers;

use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use App\Containers\LifelogSection\Post\Models\Post;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Transformer for Album in album page
 */
class PostTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'user'
    ];

    public function transform(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'datetime' => $post->datetime,
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeUser(Post $post): Item
    {
        return $this->item($post->user, new UserTransformer(), 'user');
    }
}
