<?php

namespace App\Containers\Music\Data\Repositories;

class PlaylistRepository
{
    public function getPlaylists()
    {
        return auth()->user()->playlists()->get();
    }

    public function store($requestData)
    {
        return auth()->user()->playlists()->create($requestData);
    }
}
