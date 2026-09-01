<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\DTO\CreateVideoDto;
use App\Containers\GallerySection\Video\Data\DTO\UpdateVideoDto;
use App\Containers\GallerySection\Video\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class VideoRepository
{
    public function listByAlbum(Album $album): Collection
    {
        return $album->videos()->orderByDesc('created_at')->get();
    }

    public function findSavedCopy(int $userId, int|string $albumId, string $originId): ?Video
    {
        return Video::query()
            ->where('user_id', $userId)
            ->where('album_id', $albumId)
            ->where(function ($query) use ($originId) {
                $query->where('id', $originId)->orWhere('saved_from_id', $originId);
            })
            ->first();
    }

    public function findForUser(string $id, int $userId): ?Video
    {
        return Video::query()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(CreateVideoDto $dto): Video
    {
        $video = new Video($dto->toArray());

        if ($dto->id !== null && $dto->id !== '') {
            $video->id = $dto->id;
        }

        $video->save();

        return $video;
    }

    public function update(Video $video, UpdateVideoDto $dto): Video
    {
        $attributes = [];

        if (!($dto->description instanceof Optional)) {
            $attributes['description'] = $dto->description;
        }

        if ($attributes !== []) {
            $video->update($attributes);
        }

        return $video->refresh();
    }

    public function delete(Video $video): bool
    {
        return (bool) $video->delete();
    }
}
