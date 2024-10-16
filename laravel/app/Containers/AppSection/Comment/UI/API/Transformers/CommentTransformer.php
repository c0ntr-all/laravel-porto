<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Transformers;

use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\User\UI\Transformer\UserTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'user'
    ];

    public function transform(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeUser(Comment $comment): Item
    {
        return $this->item($comment->user, new UserTransformer(), 'user');
    }
}
