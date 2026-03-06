<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Tasks;

use App\Containers\AppSection\Comment\Data\DTO\CommentListDto;
use App\Containers\AppSection\Comment\Data\Repositories\CommentRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListCommentsTask extends Task
{
    public function __construct(
        private readonly CommentRepository $commentRepository
    )
    {
    }

    public function run(CommentListDto $dto): Collection
    {
        return $this->commentRepository->get($dto->toArray());
    }
}
