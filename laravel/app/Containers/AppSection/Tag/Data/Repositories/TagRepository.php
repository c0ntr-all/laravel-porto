<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Repositories;

use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository
{
    public function get(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    public function create($dto)
    {
        return Tag::create($dto->toArray());
    }

    public function delete(string $id): int
    {
        return Tag::whereId($id)->delete();
    }
}
