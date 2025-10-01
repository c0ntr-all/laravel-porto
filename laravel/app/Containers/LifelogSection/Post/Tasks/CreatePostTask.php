<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostCreateData;
use App\Containers\LifelogSection\Post\Data\Repositories\PostRepository;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreatePostTask extends ParentTask
{
    public function __construct(
        private readonly PostRepository $postRepository
    )
    {
    }

    /**
     * @param PostCreateData $dto
     * @return Post
     */
    public function run(PostCreateData $dto): Post
    {
        return $this->postRepository->createPost([
            'user_id' => $dto->user_id,
            'title' => $dto->title,
            'content' => $dto->content,
            'datetime' => $dto->datetime
        ]);
    }
}
