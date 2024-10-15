<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Observers;

use App\Containers\MusicSection\Album\Models\AlbumType;
use Illuminate\Support\Facades\Cache;

class AlbumTypeObserver
{
    public function saved(): void
    {
        $this->updateCache();
    }

    public function deleted(): void
    {
        $this->updateCache();
    }

    /**
     * Update the cache for album version types.
     *
     * @return void
     */
    protected function updateCache(): void
    {
        Cache::put('album_types', AlbumType::all(), now()->addDay());
    }
}
