<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Repositories;

use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    public function get(array $data): Collection
    {
        return QueryBuilder::for(Post::whereUserId($data['user_id']))
                           ->allowedSorts('datetime')
                           ->with(['user'])
                           ->get();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createPost(array $data): Post
    {
        return Post::create($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }
}
