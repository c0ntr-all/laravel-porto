<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Repositories;

use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    public function getPosts(): Collection
    {
        return Post::with(['user'])->get();
    }
}
