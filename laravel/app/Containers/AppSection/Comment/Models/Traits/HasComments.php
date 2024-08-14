<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\Models\Traits;

use App\Containers\AppSection\Comment\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
