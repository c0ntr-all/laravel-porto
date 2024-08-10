<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Data\Repositories;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Ship\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class CommentRepository
{
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
