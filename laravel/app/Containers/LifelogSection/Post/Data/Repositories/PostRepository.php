<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Repositories;

use App\Containers\LifelogSection\Post\Data\Filters\DateFromFilter;
use App\Containers\LifelogSection\Post\Data\Filters\DateToFilter;
use App\Containers\LifelogSection\Post\Data\Filters\PresetFilter;
use App\Containers\LifelogSection\Post\Data\Filters\TagsFilter;
use App\Containers\LifelogSection\Post\Data\Filters\TextFilter;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class PostRepository
{
    public function __construct(
        private readonly PresetFilter $presetFilter,
        private readonly DateFromFilter $dateFromFilter,
        private readonly DateToFilter $dateToFilter,
        private readonly TextFilter $textFilter,
    ) {
    }

    public function get(array $data): Collection
    {
        return QueryBuilder::for(Post::whereUserId($data['user_id']))
                           ->allowedSorts('date')
                           ->allowedFilters([
                               AllowedFilter::custom('preset', $this->presetFilter),
                               AllowedFilter::custom('tags', new TagsFilter()),
                               // Filter exists only in tags scope
                               AllowedFilter::exact('tags_mode')->ignore(['or', 'and']),
                               AllowedFilter::exact('content_type'),
                               AllowedFilter::custom('date_from', $this->dateFromFilter),
                               AllowedFilter::custom('date_to', $this->dateToFilter),
                               AllowedFilter::custom('text', $this->textFilter),
                           ])
                           ->with(['user', 'attachments.fileable', 'movies.genres', 'movies.countries'])
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
