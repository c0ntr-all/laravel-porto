<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\Repositories;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Ship\Enums\ContainerAliasEnum;
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
                AllowedFilter::exact('commentable_id'),
                AllowedFilter::callback('commentable_type', function ($query, $value): void {
                    $query->where(
                        'commentable_type',
                        ContainerAliasEnum::toCanonicalMorphAlias((string) $value),
                    );
                }),
            ])
            ->with(['user'])
            ->get();
    }

    /**
     * @throws RepositoryException
     */
    public function create(CommentCreateData $dto): Comment
    {
        $type = ContainerAliasEnum::toCanonicalMorphAlias($dto->commentable_type);

        /** @var class-string<Model>|null $class */
        $class = Relation::getMorphedModel($type);
        if (!$class) {
            throw new RepositoryException('Class not found');
        }

        $model = $class::query()->find($dto->commentable_id);
        if (!$model) {
            throw new RepositoryException('Model not found');
        }

        if (!method_exists($model, 'comments')) {
            throw new RepositoryException('Model does not support comments');
        }

        return $model->comments()->create([
            'user_id' => $dto->user_id,
            'content' => $dto->content,
        ]);
    }
}
