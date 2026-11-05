<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Tasks;

use App\Containers\AppSection\Comment\Data\Repositories\CommentRepository;
use App\Containers\AppSection\Comment\Models\Comment;

class DeleteCommentTask
{
    public function __construct(
        private readonly CommentRepository $repository,
    ) {
    }

    public function run(Comment $comment): bool
    {
        return $this->repository->delete($comment);
    }
}
