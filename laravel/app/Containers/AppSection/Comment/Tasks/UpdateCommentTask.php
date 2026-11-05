<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Tasks;

use App\Containers\AppSection\Comment\Data\DTO\CommentUpdateData;
use App\Containers\AppSection\Comment\Data\Repositories\CommentRepository;
use App\Containers\AppSection\Comment\Models\Comment;

class UpdateCommentTask
{
    public function __construct(
        private readonly CommentRepository $repository,
    ) {
    }

    public function run(Comment $comment, CommentUpdateData $dto): Comment
    {
        return $this->repository->update($comment, $dto->content);
    }
}
