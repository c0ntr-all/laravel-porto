<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoSourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Album $album;
    private string $libraryRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->album = Album::create([
            'user_id' => $this->user->id,
            'name' => 'Clips',
        ]);

        $this->libraryRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gallery-videos-' . uniqid('', true);
        mkdir($this->libraryRoot . DIRECTORY_SEPARATOR . 'Videos', 0777, true);

        config([
            'filesystems.disks.windows_f.root' => $this->libraryRoot,
            'video.windows.disk' => 'windows_f',
            'video.windows.root_folder' => 'F:\\Videos\\',
        ]);
        Storage::forgetDisk('windows_f');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->libraryRoot);
        parent::tearDown();
    }

    public function test_user_can_register_windows_video_and_stream_it(): void
    {
        $payload = 'not-a-real-video-but-enough-for-streaming';
        $absolute = $this->libraryRoot . DIRECTORY_SEPARATOR . 'Videos' . DIRECTORY_SEPARATOR . 'clip.mp4';
        file_put_contents($absolute, $payload);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/videos/upload-windows", [
                'paths' => ['F:\\Videos\\clip.mp4'],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'gallery_videos')
            ->assertJsonPath('data.0.attributes.source', FileSourceEnum::WINDOWS->value);

        $videoId = $response->json('data.0.id');
        $this->assertStringContainsString("/api/v1/gallery/videos/{$videoId}/file", $response->json('data.0.attributes.original_path'));

        $this->assertDatabaseHas('gallery_videos', [
            'id' => $videoId,
            'album_id' => $this->album->id,
            'external_url' => 'F:\\Videos\\clip.mp4',
            'source' => FileSourceEnum::WINDOWS->value,
        ]);

        $this->assertDatabaseHas('activity_use_case_logs', [
            'loggable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
            'loggable_id' => $videoId,
            'event_type' => EventTypesEnum::CREATED->value,
        ]);

        $fileResponse = $this->actingAs($this->user, 'api')
            ->get("/api/v1/gallery/videos/{$videoId}/file");

        $fileResponse->assertOk();
        $this->assertSame($payload, $fileResponse->streamedContent());
    }

    public function test_user_can_register_web_video(): void
    {
        Http::fake([
            'https://cdn.example.com/clip.mp4' => Http::response('remote-bytes', 200, ['Content-Type' => 'video/mp4']),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/gallery/albums/{$this->album->id}/videos/upload-web", [
                'link' => 'https://cdn.example.com/clip.mp4',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'gallery_videos')
            ->assertJsonPath('data.attributes.source', FileSourceEnum::WEB->value)
            ->assertJsonPath('data.attributes.original_path', 'https://cdn.example.com/clip.mp4');
    }

    public function test_album_includes_both_images_and_videos(): void
    {
        Video::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'mp4',
            'external_url' => 'https://cdn.example.com/a.mp4',
            'width' => 1,
            'height' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/albums/{$this->album->id}");

        $response->assertOk()
            ->assertJsonPath('meta.videos_count', 1);

        $types = collect($response->json('included'))->pluck('type')->all();
        $this->assertContains('gallery_videos', $types);
    }

    public function test_user_can_list_update_and_delete_video(): void
    {
        $video = Video::create([
            'user_id' => $this->user->id,
            'album_id' => $this->album->id,
            'source' => FileSourceEnum::WEB->value,
            'extension' => 'mp4',
            'external_url' => 'https://cdn.example.com/a.mp4',
            'width' => 1,
            'height' => 1,
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/v1/gallery/albums/{$this->album->id}/videos")
            ->assertOk()
            ->assertJsonPath('meta.count', 1);

        $this->actingAs($this->user, 'api')
            ->patchJson("/api/v1/gallery/videos/{$video->id}", [
                'description' => 'Clip note',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.description', 'Clip note');

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/v1/gallery/videos/{$video->id}")
            ->assertOk();

        $this->assertDatabaseMissing('gallery_videos', ['id' => $video->id]);
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($directory);
    }
}
