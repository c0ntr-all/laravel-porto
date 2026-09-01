<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Tasks;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Data\Repositories\CommentRepository;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Ship\Exceptions\CreateResourceFailedException;
use App\Ship\Exceptions\RepositoryException;

class CreateCommentTask
{
    public function __construct(
        protected CommentRepository $repository,
    ) {
    }

    /**
     * @throws CreateResourceFailedException
     */
    public function run(CommentCreateData $dto): Comment
    {
        try {
            return $this->repository->create($dto);
        } catch (RepositoryException $exception) {
            throw new CreateResourceFailedException($exception->getMessage());
        }
    }
}
