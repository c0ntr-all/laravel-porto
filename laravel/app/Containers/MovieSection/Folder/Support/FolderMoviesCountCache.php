<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Support;

use App\Containers\MovieSection\Folder\Models\Folder;
use Illuminate\Support\Facades\Cache;

class FolderMoviesCountCache
{
    private const TTL_SECONDS = 3600;

    public static function key(int $folderId): string
    {
        return 'movie_folder:'.$folderId.':movies_count';
    }

    public static function get(Folder $folder): int
    {
        return (int) Cache::remember(
            self::key((int) $folder->id),
            self::TTL_SECONDS,
            static fn (): int => (int) $folder->movies_count,
        );
    }

    public static function remember(Folder $folder, int $count): int
    {
        $folder->forceFill(['movies_count' => $count])->saveQuietly();
        Cache::put(self::key((int) $folder->id), $count, self::TTL_SECONDS);

        return $count;
    }

    public static function increment(Folder $folder): int
    {
        $folder->increment('movies_count');

        return self::remember($folder, (int) $folder->movies_count);
    }

    public static function decrement(Folder $folder): int
    {
        $count = max(0, (int) $folder->movies_count - 1);

        return self::remember($folder, $count);
    }

    public static function forget(int $folderId): void
    {
        Cache::forget(self::key($folderId));
    }

    public static function refresh(Folder $folder): int
    {
        self::forget((int) $folder->id);

        return self::remember($folder, (int) $folder->movies()->count());
    }
}
