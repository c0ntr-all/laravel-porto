<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
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
     * @param PostCreateDto $postCreateDto
     * @return Post
     */
    public function run(PostCreateDto $postCreateDto): Post
    {
        return $this->postRepository->createPost([
            'user_id' => $postCreateDto->user_id,
            'title' => $postCreateDto->title,
            'content' => $postCreateDto->content,
            'date' => $postCreateDto->date,
            'time' => $postCreateDto->time
        ]);
    }
}
