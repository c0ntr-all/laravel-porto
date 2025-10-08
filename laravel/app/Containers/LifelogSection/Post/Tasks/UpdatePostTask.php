<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Data\Repositories\PostRepository;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdatePostTask extends ParentTask
{
    public function __construct(
        private readonly PostRepository $postRepository
    )
    {
    }

    /**
     * @param Post $post
     * @param PostUpdateDto $dto
     * @return Post
     */
    public function run(Post $post, PostUpdateDto $dto): Post
    {
        return $this->postRepository->update($post, $dto->toArray());
    }
}
