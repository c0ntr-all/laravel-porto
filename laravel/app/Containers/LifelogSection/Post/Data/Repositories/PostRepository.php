<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Repositories;

use App\Containers\LifelogSection\Post\Data\Filters\TagsFilter;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class PostRepository
{
    public function get(array $data): Collection
    {
        return QueryBuilder::for(Post::whereUserId($data['user_id']))
                           ->allowedSorts('datetime')
                           ->allowedFilters([
                               AllowedFilter::custom('tags', new TagsFilter()),
                               // Filter exists only in tags scope
                               AllowedFilter::exact('tags_mode')->ignore(['or', 'and']),
                           ])
                           ->with(['user', 'attachments.fileable'])
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
     * @param Post $post
     * @param array $data
     * @return mixed
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }
}
