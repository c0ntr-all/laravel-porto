<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Data\Repositories;

use App\Containers\GallerySection\Video\Models\Video;

class VideoRepository
{
    public function create(array $data): Video
    {
        return Video::create($data);
    }
}
