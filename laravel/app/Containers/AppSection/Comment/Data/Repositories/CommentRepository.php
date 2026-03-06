<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\Repositories;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Ship\Exceptions\RepositoryException;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\QueryBuilder\AllowedFilter;

class CommentRepository
{
    public function get(array $data): Collection
    {
        return QueryBuilder::for(Comment::class)
            ->allowedSorts('created_at')
            ->allowedFilters([
                // Явно указываем, что это фильтр точного совпадения
                AllowedFilter::exact('commentable_id'),
                AllowedFilter::exact('commentable_type'),
            ])
            ->with(['user'])
            ->get();
    }
    /**
     * @throws RepositoryException
     */
    public function create(CommentCreateData $dto)
    {
        /** @var Model $class */
        $class = Relation::getMorphedModel($dto->commentable_type);
        if (!$class) {
            throw new RepositoryException('Class not found');
        }

        $model = $class::find($dto->commentable_id);
        if (!$model) {
            throw new RepositoryException('Model not found');
        }

        return $model->comments()->create([
            'user_id' => $dto->user_id,
            'content' => $dto->content
        ]);
    }
}
