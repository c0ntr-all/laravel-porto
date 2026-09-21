<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\API\Transformers;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Support\FolderMoviesCountCache;
use League\Fractal\TransformerAbstract;

class FolderTransformer extends TransformerAbstract
{
    public function transform(Folder $folder): array
    {
        return [
            'id' => $folder->id,
            'user_id' => $folder->user_id,
            'name' => $folder->name,
            'slug' => $folder->slug,
            'is_system' => $folder->is_system,
            'movies_count' => FolderMoviesCountCache::get($folder),
            'created_at' => $folder->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $folder->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
