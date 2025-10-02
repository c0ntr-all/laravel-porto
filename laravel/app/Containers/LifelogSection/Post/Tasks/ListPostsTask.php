<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostListDto;
use App\Containers\LifelogSection\Post\Data\Repositories\PostRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListPostsTask extends Task
{
    public function __construct(
        private readonly PostRepository $postsRepository
    )
    {
    }

    public function run(PostListDto $dto): Collection
    {
        return $this->postsRepository->get([
            'user_id' => $dto->user_id
        ]);
    }
}
