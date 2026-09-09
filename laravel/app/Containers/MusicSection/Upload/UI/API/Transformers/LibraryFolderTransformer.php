<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Transformers;

use App\Containers\MusicSection\Upload\Data\DTO\LibraryFolderNode;
use League\Fractal\TransformerAbstract;

class LibraryFolderTransformer extends TransformerAbstract
{
    public function transform(LibraryFolderNode $folder): array
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'path' => $folder->path,
            'has_children' => $folder->has_children,
            'uploaded' => $folder->uploaded,
        ];
    }
}
