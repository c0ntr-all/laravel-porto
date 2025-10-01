<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Repositories;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;

class TagRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Tag::class)
                           ->allowedSorts(['name', 'created_at', 'updated_at'])
                           ->get();
    }

    public function create(array $data)
    {
        return Tag::create($data);
    }

    public function delete(string $id): int
    {
        return Tag::whereId($id)->delete();
    }
}
