<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_video_into_system_save_album(): void
    {
        $user = User::factory()->create();
        $album = Album::create([
            'user_id' => $user->id,
            'name' => 'Clips',
        ]);
        $saveAlbum = Album::withoutGlobalScopes()->firstOrCreate(
            ['system_code' => SystemAlbumsEnum::SAVE->value],
            ['name' => 'Save', 'user_id' => null],
        );
        $video = Video::create([
            'user_id' => $user->id,
            'album_id' => $album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'mp4',
            'external_url' => 'https://cdn.example.com/a.mp4',
            'width' => 10,
            'height' => 10,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/v1/gallery/videos/{$video->id}/save");

        $response->assertCreated()
            ->assertJsonPath('data.attributes.album_id', (string) $saveAlbum->id)
            ->assertJsonPath('data.attributes.original_path', 'https://cdn.example.com/a.mp4');

        $this->assertDatabaseHas('gallery_videos', [
            'id' => $response->json('data.id'),
            'album_id' => $saveAlbum->id,
            'saved_from_id' => $video->id,
            'user_id' => $user->id,
        ]);
    }
}
