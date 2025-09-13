<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Repositories;

use App\Containers\LifelogSection\Post\Data\DTO\PostCreateData;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    public function getPosts(): Collection
    {
        return QueryBuilder::for(Post::class)
                           ->allowedSorts('datetime')
                           ->with(['user'])
                           ->get();
    }

    /**
     * @param PostCreateData $dto
     * @return mixed
     */
    public function createPost(PostCreateData $dto): Post
    {
        return Post::create($dto->toArray());
    }
}
