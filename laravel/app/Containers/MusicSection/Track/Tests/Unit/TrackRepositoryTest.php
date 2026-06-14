<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tests\Unit;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_with_cursor_returns_paginated_tracks(): void
    {
        $album = Album::create([
            'name' => 'Test Album',
            'path' => '/music/test-album',
            'album_type_id' => 1,
        ]);

        Track::create([
            'album_id' => $album->id,
            'name' => 'Track 1',
            'number' => 1,
        ]);
        Track::create([
            'album_id' => $album->id,
            'name' => 'Track 2',
            'number' => 2,
        ]);

        $repo = app(TrackRepository::class);
        $result = $repo->getWithCursor();

        $this->assertCount(2, $result->items());
    }

    public function test_updateOrCreate_creates_new_track(): void
    {
        $album = Album::create([
            'name' => 'Test Album',
            'path' => '/music/test-album',
            'album_type_id' => 1,
        ]);

        $repo = app(TrackRepository::class);
        $dto = \App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto::from([
            'name' => 'New Track',
            'number' => 1,
            'duration' => '03:30',
        ]);

        $track = $repo->updateOrCreate($album, $dto);

        $this->assertEquals('New Track', $track->name);
        $this->assertEquals($album->id, $track->album_id);
        $this->assertDatabaseHas('music_tracks', ['name' => 'New Track']);
    }

    public function test_updateOrCreate_updates_existing_track(): void
    {
        $album = Album::create([
            'name' => 'Test Album',
            'path' => '/music/test-album',
            'album_type_id' => 1,
        ]);

        Track::create([
            'album_id' => $album->id,
            'name' => 'Existing Track',
            'number' => 1,
        ]);

        $repo = app(TrackRepository::class);
        $dto = \App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto::from([
            'name' => 'Existing Track',
            'number' => 2,
            'duration' => '04:00',
        ]);

        $track = $repo->updateOrCreate($album, $dto);

        $this->assertEquals(2, $track->number);
        $this->assertEquals('04:00', (string) $track->duration);
    }
}
