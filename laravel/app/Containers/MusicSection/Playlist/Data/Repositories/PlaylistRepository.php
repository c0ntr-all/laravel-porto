<?php

namespace App\Containers\MusicSection\Playlist\Data\Repositories;

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
